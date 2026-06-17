<?php

namespace Database\Seeders;

use App\Models\Trading\Asset;
use App\Models\Trading\Trade;
use App\Models\Trading\TradingSetting;
use App\Models\Trading\Wallet;
use App\Models\Trading\WalletEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Activitylog\ActivityLogStatus;

/**
 * Master synthetic-data seeder for the Cryptocoinex trading simulator.
 *
 * Produces 50 student accounts, each with a funded wallet, a chronological
 * sequence of settled trades, a consistent double-entry ledger, and some
 * journalled (annotated) trades. All data is 100% fake — no real money.
 *
 * Idempotent-ish: deletes previously-seeded demo students (emails ending in
 *
 * @cryptocoinex.demo) before re-seeding, so it can be run repeatedly.
 */
class CryptocoineuxSeeder extends Seeder
{
    private const STUDENT_COUNT = 50;

    private const EMAIL_DOMAIN = 'cryptocoinex.demo';

    /** Country => IANA timezone. */
    private const COUNTRIES = [
        'Uganda' => 'Africa/Kampala', 'Kenya' => 'Africa/Nairobi', 'Nigeria' => 'Africa/Lagos',
        'Ghana' => 'Africa/Accra', 'South Africa' => 'Africa/Johannesburg', 'Tanzania' => 'Africa/Dar_es_Salaam',
        'Rwanda' => 'Africa/Kigali', 'Egypt' => 'Africa/Cairo', 'United Kingdom' => 'Europe/London',
        'United States' => 'America/New_York', 'Canada' => 'America/Toronto', 'India' => 'Asia/Kolkata',
        'Pakistan' => 'Asia/Karachi', 'UAE' => 'Asia/Dubai', 'Germany' => 'Europe/Berlin',
        'France' => 'Europe/Paris', 'Brazil' => 'America/Sao_Paulo', 'Philippines' => 'Asia/Manila',
        'Indonesia' => 'Asia/Jakarta', 'Australia' => 'Australia/Sydney',
    ];

    private const FIRST_NAMES = [
        'Aisha', 'Brian', 'Chen', 'Diana', 'Emeka', 'Fatima', 'Grace', 'Hassan', 'Ivan', 'Joy',
        'Kwame', 'Lucia', 'Musa', 'Nadia', 'Omar', 'Priya', 'Rashid', 'Sara', 'Tendai', 'Uchenna',
        'Victor', 'Wanjiru', 'Xavier', 'Yusuf', 'Zara', 'Daniel', 'Esther', 'Felix', 'Halima', 'Isaac',
    ];

    private const LAST_NAMES = [
        'Okello', 'Mensah', 'Wang', 'Smith', 'Okafor', 'Bello', 'Mwangi', 'Ali', 'Petrov', 'Achieng',
        'Asante', 'Rossi', 'Diallo', 'Khan', 'Mueller', 'Dubois', 'Silva', 'Cruz', 'Moyo', 'Eze',
    ];

    private const EXPERIENCES = ['beginner', 'beginner', 'beginner', 'intermediate', 'intermediate', 'advanced'];

    private const SENTIMENTS = ['confident', 'unsure', 'fomo'];

    private const NOTE_SNIPPETS = [
        'Followed the trend on this one.', 'Bit of a gamble, but the momentum looked right.',
        'Should have waited for confirmation.', 'News-driven spike — jumped in late.',
        'Clean breakout, textbook entry.', 'Revenge trade. Note to self: do not do this.',
        'Scalped the dip, worked out nicely.', 'Overtraded today, taking a break.',
        'Stuck to my plan and it paid off.', 'Volatility was brutal here.',
    ];

    public function run(): void
    {
        // Disable activity logging during the bulk insert — keeps seeding fast
        // and the audit trail clean (it should reflect real usage, not seeding).
        app(ActivityLogStatus::class)->disable();

        $assets = Asset::where('enabled', true)->get();
        if ($assets->isEmpty()) {
            $this->command->warn('No enabled assets found — run TradingSeeder first. Aborting.');

            return;
        }

        $startBalance = (int) TradingSetting::get('default_start_balance', 1000);

        // Clean previous demo students (cascade removes their wallets/trades/entries).
        User::where('email', 'like', '%@'.self::EMAIL_DOMAIN)->get()
            ->each(fn (User $u) => $u->delete());

        $this->command->info('Seeding '.self::STUDENT_COUNT.' students with trades…');

        $tradeTotal = 0;
        $usedNames = [];

        for ($i = 1; $i <= self::STUDENT_COUNT; $i++) {
            $name = $this->uniqueName($usedNames);
            [$country, $tz] = $this->randomCountry();

            $joinedAt = Carbon::now()->subDays(rand(7, 90))->setTime(rand(7, 21), rand(0, 59));

            $user = User::create([
                'name' => $name,
                'username' => Str::slug($name).rand(10, 999),
                'email' => Str::slug($name, '.').$i.'@'.self::EMAIL_DOMAIN,
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_admin' => false,
                'is_active' => true,
                'country' => $country,
                'city' => null,
                'timezone' => $tz,
                'trading_experience' => self::EXPERIENCES[array_rand(self::EXPERIENCES)],
                'preferred_assets' => $assets->random(rand(1, min(3, $assets->count())))
                    ->pluck('symbol')->values()->all(),
                'notification_prefs' => ['email' => (bool) rand(0, 1), 'in_app' => true, 'sounds' => (bool) rand(0, 1)],
                'last_active_at' => Carbon::now()->subMinutes(rand(5, 60 * 24 * 7)),
                'created_at' => $joinedAt,
                'updated_at' => $joinedAt,
            ]);

            $tradeTotal += $this->seedWalletAndTrades($user, $assets, $startBalance, $joinedAt);
        }

        app(ActivityLogStatus::class)->enable();

        $this->command->info('Done: '.self::STUDENT_COUNT." students, {$tradeTotal} trades.");
    }

    /**
     * Build a wallet + chronological trade sequence for one student.
     * Returns the number of trades created.
     */
    private function seedWalletAndTrades(User $user, $assets, int $startBalance, Carbon $joinedAt): int
    {
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency_label' => 'USD',
        ]);

        $balance = 0;
        $peak = 0;
        $totalCredited = 0;
        $totalDebited = 0;
        $entries = [];

        // Starting balance grant.
        $balance += $startBalance;
        $totalCredited += $startBalance;
        $peak = max($peak, $balance);
        $entries[] = $this->entry($wallet->id, null, 'topup', $startBalance, $balance, ['reason' => 'starting_balance'], $joinedAt);

        $tradeCount = rand(4, 8);
        $cursor = $joinedAt->copy()->addHours(rand(1, 12));

        for ($t = 0; $t < $tradeCount; $t++) {
            if ($balance < 50) {
                break; // out of funds
            }

            /** @var Asset $asset */
            $asset = $assets->random();
            $direction = rand(0, 1) ? 'up' : 'down';
            $mode = rand(1, 100) <= 90 ? 'sim' : ($asset->supports_live ? 'live' : 'sim');
            $maxStake = min((int) $asset->max_stake, $balance, 2000);
            $minStake = max(1, (int) $asset->min_stake);
            if ($maxStake < $minStake) {
                break;
            }
            $stake = rand($minStake, $maxStake);

            $expiries = $asset->allowed_expiries ?: [30, 60, 300];
            $expiry = $expiries[array_rand($expiries)];

            $openedAt = $cursor->copy();
            $expiresAt = $openedAt->copy()->addSeconds($expiry);
            $settledAt = $expiresAt->copy()->addSeconds(rand(1, 3));

            // Outcome: 45% won / 45% lost / 10% tie
            $roll = rand(1, 100);
            $status = $roll <= 45 ? 'won' : ($roll <= 90 ? 'lost' : 'tie');

            [$entryPrice, $exitPrice] = $this->prices($asset, $direction, $status);

            // Debit stake (hold).
            $balance -= $stake;
            $totalDebited += $stake;
            $trade = Trade::create([
                'user_id' => $user->id,
                'asset_id' => $asset->id,
                'mode' => $mode,
                'direction' => $direction,
                'stake' => $stake,
                'payout_percent' => $asset->payout_percent,
                'entry_price' => $entryPrice,
                'exit_price' => $exitPrice,
                'opened_at' => $openedAt,
                'expires_at' => $expiresAt,
                'settled_at' => $settledAt,
                'expiry_seconds' => $expiry,
                'status' => $status,
                'payout_amount' => null,
                'device_type' => rand(0, 1) ? 'desktop' : 'mobile',
                'sentiment' => rand(0, 2) ? null : self::SENTIMENTS[array_rand(self::SENTIMENTS)],
                'created_at' => $openedAt,
                'updated_at' => $settledAt,
            ]);

            $entries[] = $this->entry($wallet->id, $trade->id, 'stake_hold', -$stake, $balance, ['trade_id' => $trade->id], $openedAt);

            // Settlement credit.
            $payoutAmount = null;
            if ($status === 'won') {
                $payoutAmount = $stake + (int) round($stake * $asset->payout_percent / 100);
                $balance += $payoutAmount;
                $totalCredited += $payoutAmount;
                $entries[] = $this->entry($wallet->id, $trade->id, 'payout', $payoutAmount, $balance, ['outcome' => 'won'], $settledAt);
            } elseif ($status === 'tie') {
                $payoutAmount = $stake;
                $balance += $stake;
                $totalCredited += $stake;
                $entries[] = $this->entry($wallet->id, $trade->id, 'refund', $stake, $balance, ['outcome' => 'tie'], $settledAt);
            }

            if ($payoutAmount !== null) {
                $trade->payout_amount = $payoutAmount;
            }

            // ~30% of settled trades get a journal note.
            if (rand(1, 100) <= 30) {
                $trade->notes = self::NOTE_SNIPPETS[array_rand(self::NOTE_SNIPPETS)];
                $trade->tags = collect(['btc-dip', 'news-event', 'fomo', 'breakout', 'scalp', 'swing'])
                    ->random(rand(1, 2))->values()->all();
            }
            $trade->saveQuietly();

            $peak = max($peak, $balance);
            $cursor = $settledAt->copy()->addMinutes(rand(10, 60 * 36));
        }

        WalletEntry::insert($entries);

        $wallet->update([
            'balance' => $balance,
            'peak_balance' => $peak,
            'total_credited' => $totalCredited,
            'total_debited' => $totalDebited,
            'resets_count' => 0,
        ]);

        return $tradeCount;
    }

    private function entry(int $walletId, ?int $tradeId, string $type, int $amount, int $balanceAfter, array $meta, Carbon $at): array
    {
        return [
            'wallet_id' => $walletId,
            'trade_id' => $tradeId,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'meta' => json_encode($meta),
            'created_at' => $at,
        ];
    }

    /**
     * Produce realistic entry/exit prices consistent with direction + outcome.
     *
     * @return array{0: float, 1: float}
     */
    private function prices(Asset $asset, string $direction, string $status): array
    {
        $base = (float) $asset->sim_start_price;
        // Entry within ±0.4% of the sim anchor.
        $entry = $base * (1 + (rand(-40, 40) / 10000));

        if ($status === 'tie') {
            return [round($entry, 8), round($entry, 8)];
        }

        // Magnitude of the move: 0.05%–0.6%.
        $move = $entry * (rand(5, 60) / 10000);

        $priceWentUp = match (true) {
            $status === 'won' && $direction === 'up' => true,
            $status === 'won' && $direction === 'down' => false,
            $status === 'lost' && $direction === 'up' => false,
            default => true, // lost + down => price went up
        };

        $exit = $priceWentUp ? $entry + $move : $entry - $move;

        return [round($entry, 8), round(max(0.00000001, $exit), 8)];
    }

    private function uniqueName(array &$used): string
    {
        do {
            $name = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)].' '.self::LAST_NAMES[array_rand(self::LAST_NAMES)];
        } while (isset($used[$name]));
        $used[$name] = true;

        return $name;
    }

    /** @return array{0: string, 1: string} */
    private function randomCountry(): array
    {
        $country = array_rand(self::COUNTRIES);

        return [$country, self::COUNTRIES[$country]];
    }
}
