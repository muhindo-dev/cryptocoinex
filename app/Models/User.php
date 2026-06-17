<?php

namespace App\Models;

use App\Models\Trading\Trade;
use App\Models\Trading\Wallet;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'phone', 'bio', 'avatar', 'is_active', 'is_admin',
        // Trading profile fields
        'date_of_birth', 'gender', 'country', 'city', 'timezone', 'trading_experience',
        'preferred_assets', 'notification_prefs', 'last_active_at',
        'cover_photo', 'twitter_handle', 'instagram_handle', 'theme',
        // KYC (identity verification)
        'kyc_status', 'kyc_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'date_of_birth' => 'date',
            'preferred_assets' => 'array',
            'notification_prefs' => 'array',
            'last_active_at' => 'datetime',
            'kyc_verified_at' => 'datetime',
        ];
    }

    // ── KYC (identity verification) ────────────────────────────

    public function kycSubmissions(): HasMany
    {
        return $this->hasMany(\App\Models\KycSubmission::class)->latest();
    }

    public function latestKyc(): ?\App\Models\KycSubmission
    {
        return $this->kycSubmissions()->first();
    }

    /** True once the user has passed identity verification. */
    public function isKycApproved(): bool
    {
        return $this->kyc_status === 'approved';
    }

    /** Admins are exempt; everyone else must be approved to use live features. */
    public function requiresKyc(): bool
    {
        return ! $this->isAdmin() && ! $this->isKycApproved();
    }

    /** Can the user start (or restart) a verification right now? */
    public function canSubmitKyc(): bool
    {
        return in_array($this->kyc_status, ['unverified', 'declined', 'resubmit'], true);
    }

    // ── Avatar helper ──────────────────────────────────────────

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/'.$this->avatar);
        }

        return null;
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', trim($this->name ?? 'U'));
        $initials = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1) {
            $initials .= strtoupper(substr(end($parts), 0, 1));
        }

        return $initials;
    }

    // ── Password reset ─────────────────────────────────────────

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    // ── Role helpers ───────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->is_admin === true;
    }

    /** Roles that may reach the admin/instructor back office. */
    public const STAFF_ROLES = ['admin', 'instructor', 'moderator', 'officer', 'frontdesk'];

    protected static function booted(): void
    {
        // Keep the Spatie role in sync with the legacy `role` column so that
        // permission-based checks work everywhere without a separate workflow.
        static::saved(function (User $user) {
            if ($user->wasChanged('role') || $user->wasRecentlyCreated) {
                $user->syncSpatieRole();
            }
        });
    }

    /** The canonical Spatie role name for this user's legacy `role` column. */
    public function spatieRoleName(): string
    {
        return match ($this->role) {
            'admin' => 'admin',
            'instructor', 'officer' => 'instructor',
            'moderator', 'frontdesk' => 'moderator',
            default => 'student',
        };
    }

    /** Mirror the legacy role into Spatie roles (no-op if roles aren't seeded). */
    public function syncSpatieRole(): void
    {
        $name = $this->spatieRoleName();
        if (\Spatie\Permission\Models\Role::where('name', $name)->where('guard_name', 'web')->exists()) {
            $this->syncRoles([$name]);
        }
    }

    public function isInstructor(): bool
    {
        // Legacy "officer" is treated as an instructor in trading mode.
        return in_array($this->role, ['instructor', 'officer'], true);
    }

    public function isModerator(): bool
    {
        // Legacy "frontdesk" is treated as a moderator in trading mode.
        return in_array($this->role, ['moderator', 'frontdesk'], true);
    }

    public function isStudent(): bool
    {
        return $this->role === 'student' || $this->role === null;
    }

    // ── Backward-compat aliases (legacy legal app) ──
    public function isOfficer(): bool
    {
        return $this->isInstructor();
    }

    public function isFrontdesk(): bool
    {
        return $this->isModerator();
    }

    public function canAccessAdmin(): bool
    {
        return in_array($this->role, self::STAFF_ROLES, true) || $this->is_admin;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'instructor', 'officer' => 'Instructor',
            'moderator', 'frontdesk' => 'Moderator',
            'student' => 'Student',
            default => ucfirst($this->role ?? 'Student'),
        };
    }

    // ── Trading relations ──

    public function tradingWallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /** The user's real-money Live Account wallet. */
    public function liveWallet(): HasOne
    {
        return $this->hasOne(\App\Models\Trading\LiveWallet::class);
    }

    public function depositRequests(): HasMany
    {
        return $this->hasMany(\App\Models\Trading\DepositRequest::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(\App\Models\Trading\WithdrawalRequest::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(\App\Models\Trading\Achievement::class);
    }

    /** In-app trading notifications (distinct from Laravel's Notifiable::notifications). */
    public function tradingNotifications(): HasMany
    {
        return $this->hasMany(\App\Models\Trading\TradingNotification::class);
    }

    public function leaderboardSnapshots(): HasMany
    {
        return $this->hasMany(\App\Models\Trading\LeaderboardSnapshot::class);
    }
}
