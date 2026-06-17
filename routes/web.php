<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Trading\AssetController as TradingAssetController;
use App\Http\Controllers\Admin\Trading\SettingController as TradingSettingController;
use App\Http\Controllers\Admin\Trading\StudentController as TradingStudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Trading\FeedController;
use App\Http\Controllers\Trading\TradeController;
use App\Http\Controllers\Trading\WalletController;
use Illuminate\Support\Facades\Route;

// ── Public ─────────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->canAccessAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('trade.index');
    }

    return view('public.home', [
        'assetCount' => \App\Models\Trading\Asset::where('enabled', true)->count() ?: 13,
        'lessonCount' => \App\Models\Education\EducationArticle::count() ?: 42,
        'startBalance' => (int) \App\Models\Trading\TradingSetting::get('default_start_balance', 10000),
        'currency' => \App\Models\Trading\TradingSetting::get('live_account_currency', 'USD'),
        'minDeposit' => (float) \App\Models\Trading\TradingSetting::get('live_account_min_deposit', 0),
        'maxPayout' => (int) (\App\Models\Trading\Asset::where('enabled', true)->max('payout_percent') ?: 80),
    ]);
})->name('home');

// ── Public info / legal ───────────────────────────────────────────────────────
Route::get('/privacy', fn () => view('public.privacy'))->name('privacy');
Route::get('/terms', fn () => view('public.terms'))->name('terms');

// ── Auth (login serves both students and staff) ───────────────────────────────
Route::get('/admin/login', fn () => view('auth.login'))->name('admin.login');
Route::post('/admin/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
Route::post('/admin/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

// ── Student onboarding ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register', [\App\Http\Controllers\Trading\OnboardingController::class, 'register'])->name('onboarding.register');
    Route::post('/register', [\App\Http\Controllers\Trading\OnboardingController::class, 'store'])->name('onboarding.store');
});
Route::middleware('auth')->group(function () {
    Route::get('/welcome', [\App\Http\Controllers\Trading\OnboardingController::class, 'profile'])->name('onboarding.profile');
    Route::post('/welcome', [\App\Http\Controllers\Trading\OnboardingController::class, 'saveProfile'])->name('onboarding.profile.save');
});

// ── Admin Protected ─────────────────────────────────────────────────────────
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Users (admin only) ─────────────────────────────────────
    Route::resource('users', UserController::class);

    // ── Trading admin ───────────────────────────────────────────
    Route::prefix('trading')->name('trading.')->group(function () {
        Route::get('/', [TradingSettingController::class, 'overview'])->name('overview');
        Route::post('assets/{asset}/toggle', [TradingAssetController::class, 'toggle'])->name('assets.toggle');
        Route::resource('assets', TradingAssetController::class);

        Route::get('students', [TradingStudentController::class, 'index'])->name('students.index');
        Route::get('students/{student}', [TradingStudentController::class, 'show'])->name('students.show');
        Route::post('students/{student}/topup', [TradingStudentController::class, 'topup'])->name('students.topup');
        Route::post('students/{student}/reset', [TradingStudentController::class, 'reset'])->name('students.reset');
        Route::post('students/{student}/wipe', [TradingStudentController::class, 'wipe'])->name('students.wipe');

        Route::get('settings', [TradingSettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [TradingSettingController::class, 'update'])->name('settings.update');

        Route::get('activity', [\App\Http\Controllers\Admin\Trading\ActivityController::class, 'index'])->name('activity');

        // ── Live Account (real money) ──
        Route::prefix('live')->name('live.')->controller(\App\Http\Controllers\Admin\Trading\LiveController::class)->group(function () {
            Route::get('/', 'overview')->name('overview');
            Route::get('deposits', 'deposits')->name('deposits');
            Route::post('deposits/{deposit}/approve', 'approveDeposit')->name('deposits.approve');
            Route::post('deposits/{deposit}/decline', 'declineDeposit')->name('deposits.decline');
            Route::get('withdrawals', 'withdrawals')->name('withdrawals');
            Route::post('withdrawals/{withdrawal}/approve', 'approveWithdrawal')->name('withdrawals.approve');
            Route::post('withdrawals/{withdrawal}/decline', 'declineWithdrawal')->name('withdrawals.decline');
            Route::get('accounts', 'accounts')->name('accounts');
            Route::get('accounts/{wallet}', 'account')->name('accounts.show');
            Route::get('settings', 'settings')->name('settings');
            Route::post('settings', 'updateSettings')->name('settings.update');
        });

        // Profit distributions (payouts)
        Route::prefix('live/distributions')->name('live.distributions.')
            ->controller(\App\Http\Controllers\Admin\Trading\LiveDistributionController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{distribution}', 'show')->name('show');
            });

        // KYC (identity verification) review
        Route::prefix('kyc')->name('kyc.')->controller(\App\Http\Controllers\Admin\Trading\KycController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{kyc}/document', 'document')->name('document');
            Route::post('{kyc}/approve', 'approve')->name('approve');
            Route::post('{kyc}/decline', 'decline')->name('decline');
            Route::post('{kyc}/redo', 'redo')->name('redo');
        });

        // Tournaments
        Route::post('tournaments/{tournament}/end', [\App\Http\Controllers\Admin\Trading\TournamentController::class, 'end'])->name('tournaments.end');
        Route::resource('tournaments', \App\Http\Controllers\Admin\Trading\TournamentController::class)->except(['edit', 'update']);
    });
});

// ── Admin JSON API (self-profile drawer) ──────────────────────────────────────
Route::prefix('admin/api')->middleware(['auth', 'admin'])->name('admin.api.')->group(function () {
    Route::post('profile/update', [\App\Http\Controllers\Admin\ApiController::class, 'profileUpdate'])->name('profile.update');
    Route::post('profile/avatar', [\App\Http\Controllers\Admin\ApiController::class, 'profileAvatarUpdate'])->name('profile.avatar');
    Route::delete('profile/avatar', [\App\Http\Controllers\Admin\ApiController::class, 'profileAvatarRemove'])->name('profile.avatar.remove');
    Route::post('profile/password', [\App\Http\Controllers\Admin\ApiController::class, 'profilePasswordChange'])->name('profile.password');
});

// ── Trading (student) ───────────────────────────────────────────────────────
Route::prefix('trade')->middleware(['auth'])->name('trade.')->group(function () {
    Route::get('/', [TradeController::class, 'index'])->name('index');

    // Feed endpoints (polled ~1s by the chart)
    Route::get('feed', [FeedController::class, 'feed'])->name('feed');
    Route::get('price', [FeedController::class, 'price'])->name('price');

    // Wallet  (must precede {trade} wildcard)
    Route::get('wallet/ledger', [WalletController::class, 'ledger'])->name('wallet.ledger');
    Route::get('wallet/page', [WalletController::class, 'page'])->name('wallet.page');
    Route::post('wallet/reset', [WalletController::class, 'reset'])->name('wallet.reset');
    Route::post('wallet/wipe', [WalletController::class, 'wipe'])->name('wallet.wipe');
    Route::get('wallet', [WalletController::class, 'show'])->name('wallet');

    // Profile
    Route::get('profile', [\App\Http\Controllers\Trading\ProfileController::class, 'show'])->name('profile');
    Route::post('profile', [\App\Http\Controllers\Trading\ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/password', [\App\Http\Controllers\Trading\ProfileController::class, 'password'])->name('profile.password');
    Route::post('theme', [\App\Http\Controllers\Trading\ProfileController::class, 'theme'])->name('theme');

    // Live Account (real money)
    Route::get('live', [\App\Http\Controllers\Trading\LiveAccountController::class, 'index'])->name('live');
    Route::get('live/transactions', [\App\Http\Controllers\Trading\LiveAccountController::class, 'transactions'])->name('live.transactions');
    Route::get('live/deposit', [\App\Http\Controllers\Trading\LiveAccountController::class, 'deposit'])->name('live.deposit');
    Route::post('live/deposit', [\App\Http\Controllers\Trading\LiveAccountController::class, 'storeDeposit'])->name('live.deposit.store');
    Route::get('live/withdraw', [\App\Http\Controllers\Trading\LiveAccountController::class, 'withdraw'])->name('live.withdraw');
    Route::post('live/withdraw', [\App\Http\Controllers\Trading\LiveAccountController::class, 'storeWithdrawal'])->name('live.withdraw.store');

    // KYC (identity verification) — required for live features
    Route::get('kyc', [\App\Http\Controllers\Trading\KycController::class, 'show'])->name('kyc');
    Route::post('kyc', [\App\Http\Controllers\Trading\KycController::class, 'store'])->name('kyc.store');

    // Leaderboard
    Route::get('leaderboard', [\App\Http\Controllers\Trading\LeaderboardController::class, 'index'])->name('leaderboard');

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\Trading\NotificationController::class, 'index'])->name('notifications');
    Route::post('notifications/read', [\App\Http\Controllers\Trading\NotificationController::class, 'readAll'])->name('notifications.read');

    // Journal
    Route::get('journal', [\App\Http\Controllers\Trading\JournalController::class, 'page'])->name('journal');

    // Education
    Route::get('education', [\App\Http\Controllers\Trading\EducationController::class, 'index'])->name('education.index');
    Route::get('education/{article}', [\App\Http\Controllers\Trading\EducationController::class, 'show'])->name('education.show');
    Route::post('education/{article}/complete', [\App\Http\Controllers\Trading\EducationController::class, 'complete'])->name('education.complete');

    // Tournaments
    Route::get('tournaments', [\App\Http\Controllers\Trading\TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('tournaments/{tournament}', [\App\Http\Controllers\Trading\TournamentController::class, 'show'])->name('tournaments.show');
    Route::post('tournaments/{tournament}/join', [\App\Http\Controllers\Trading\TournamentController::class, 'join'])->name('tournaments.join');

    // Full history page + CSV export (before {trade} wildcard)
    Route::get('history', [\App\Http\Controllers\Trading\HistoryController::class, 'page'])->name('history.page');
    Route::get('history/export', [\App\Http\Controllers\Trading\HistoryController::class, 'export'])->name('history.export');

    // Trade lifecycle (specific paths before wildcard)
    Route::get('account', [TradeController::class, 'accountState'])->name('account');
    Route::post('place', [TradeController::class, 'place'])->name('place');
    Route::get('open', [TradeController::class, 'openPositions'])->name('openlist');
    Route::post('{trade}/close', [TradeController::class, 'close'])->name('close');
    Route::get('history/list', [TradeController::class, 'history'])->name('history');
    Route::post('{trade}/note', [\App\Http\Controllers\Trading\JournalController::class, 'saveNote'])->name('note');
    Route::get('{trade}', [TradeController::class, 'show'])->name('show');
});

require __DIR__.'/auth.php';
