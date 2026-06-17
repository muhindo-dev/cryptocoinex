<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeCredentials;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(private readonly AvatarService $avatars) {}

    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,instructor,moderator,student,officer,frontdesk',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'password' => 'required|string|min:4|confirmed',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|max:10240',
        ]);

        $plainPassword = $data['password'];
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_admin'] = $data['role'] === 'admin';

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->avatars->storeResized($request->file('avatar'));
        }

        $user = User::create($data);

        // Send welcome credentials email
        try {
            Mail::to($user->email)->send(new WelcomeCredentials($user, $plainPassword));
            $emailNote = " Login credentials sent to {$user->email}.";
        } catch (\Exception $e) {
            \Log::error("Welcome email failed for {$user->email}: ".$e->getMessage());
            $emailNote = ' (Email delivery failed — please share credentials manually.)';
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created.{$emailNote}");
    }

    public function show(User $user)
    {
        $user->load('tradingWallet');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => "required|email|unique:users,email,{$user->id}",
            'role' => 'required|in:admin,instructor,moderator,student,officer,frontdesk',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'password' => 'nullable|string|min:4|confirmed',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|max:10240',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_admin'] = $data['role'] === 'admin';

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $this->avatars->storeResized($request->file('avatar'));
        }

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = null;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} deleted.");
    }
}
