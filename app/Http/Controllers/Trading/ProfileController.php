<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Services\AvatarService;
use App\Services\Trading\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AchievementService $achievements,
        private readonly AvatarService $avatars,
    ) {}

    /** GET /trade/profile */
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $stats = $this->achievements->stats($user);

        $earned = $user->achievements()->orderByDesc('achieved_at')->get()->keyBy('type');
        $catalog = AchievementService::CATALOG;

        return view('trading.profile', compact('user', 'stats', 'earned', 'catalog'));
    }

    /** POST /trade/profile */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'trading_experience' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'twitter_handle' => ['nullable', 'string', 'max:100'],
            'instagram_handle' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:10240'],
            'notify_email' => ['nullable', 'boolean'],
            'notify_in_app' => ['nullable', 'boolean'],
            'notify_sounds' => ['nullable', 'boolean'],
        ]);

        $data['notification_prefs'] = [
            'email' => $request->boolean('notify_email'),
            'in_app' => $request->boolean('notify_in_app'),
            'sounds' => $request->boolean('notify_sounds'),
        ];
        unset($data['notify_email'], $data['notify_in_app'], $data['notify_sounds']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $this->avatars->storeResized($request->file('avatar'));
        }

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    /** POST /trade/theme — persist the user's theme preference. */
    public function theme(Request $request)
    {
        $data = $request->validate(['theme' => ['required', Rule::in(['dark', 'light'])]]);
        Auth::user()->update(['theme' => $data['theme']]);

        return response()->json(['theme' => $data['theme']]);
    }

    /** POST /trade/profile/password */
    public function password(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed.');
    }
}
