<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Trading\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Student onboarding: a dedicated, branded 3-step flow —
 *   1. Register (name / email / password)
 *   2. Profile setup (country / timezone / experience)
 *   3. Land on the trading screen with a welcome tour.
 */
class OnboardingController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    /** GET /register — step 1. */
    public function register()
    {
        return view('auth.register');
    }

    /** POST /register — create the student, fund the wallet, sign in. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $this->uniqueUsername($data['email'], $data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
            'is_admin' => false,
            'is_active' => true,
            'last_active_at' => now(),
        ]);

        // Fund the practice wallet immediately so step 3 is ready to trade.
        $this->wallets->grantStartingBalance($user->id);

        Auth::login($user);

        return redirect()->route('onboarding.profile');
    }

    /** Generate a unique username from the email local-part (or name). */
    private function uniqueUsername(string $email, string $name): string
    {
        $base = Str::slug(Str::before($email, '@') ?: $name, '_') ?: 'trader';
        $base = substr($base, 0, 40);
        $username = $base;
        while (User::where('username', $username)->exists()) {
            $username = $base.random_int(10, 9999);
        }

        return $username;
    }

    /** GET /welcome — step 2: profile setup. */
    public function profile()
    {
        return view('trading.onboarding.profile', [
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    /** POST /welcome — save profile, head to the trading screen. */
    public function saveProfile(Request $request)
    {
        $data = $request->validate([
            'country' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'trading_experience' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
        ]);

        Auth::user()->update($data + ['last_active_at' => now()]);

        return redirect()->route('trade.index', ['welcome' => 1]);
    }
}
