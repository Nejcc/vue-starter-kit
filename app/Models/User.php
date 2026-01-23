<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\TracksLastLogin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TracksLastLogin, TwoFactorAuthenticatable;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Clean up related data when user is being deleted
        self::deleting(function (User $user): void {
            // Remove all role assignments
            $user->roles()->detach();

            // Remove all permission assignments
            $user->permissions()->detach();

            // Note: audit_logs.user_id will be set to null automatically via nullOnDelete()
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cookie_consent_preferences',
        'cookie_consent_given_at',
        'data_processing_consent',
        'data_processing_consent_given_at',
        'gdpr_ip_address',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'cookie_consent_preferences' => 'array',
            'cookie_consent_given_at' => 'datetime',
            'data_processing_consent' => 'boolean',
            'data_processing_consent_given_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full name with property hook.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get the user's initials with property hook.
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';

        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= mb_strtoupper(mb_substr($name, 0, 1));
            }
        }

        return $initials;
    }

    /**
     * Check if user has verified email with property hook.
     */
    public function getHasVerifiedEmailAttribute(): bool
    {
        return null !== $this->email_verified_at;
    }

    /**
     * Get user's display name (name or email) with property hook.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->email;
    }

    /**
     * Check if user has given cookie consent.
     */
    public function hasCookieConsent(): bool
    {
        return null !== $this->cookie_consent_given_at;
    }

    /**
     * Check if user has given data processing consent.
     */
    public function hasDataProcessingConsent(): bool
    {
        return $this->data_processing_consent && null !== $this->data_processing_consent_given_at;
    }

    /**
     * Get cookie consent preferences for a specific category.
     */
    public function hasCookieConsentForCategory(string $category): bool
    {
        if (!$this->hasCookieConsent()) {
            return false;
        }

        $preferences = $this->cookie_consent_preferences ?? [];

        return $preferences[$category] ?? false;
    }

    /**
     * Update cookie consent preferences.
     */
    public function updateCookieConsent(array $preferences, ?string $ipAddress = null): void
    {
        $this->update([
            'cookie_consent_preferences' => $preferences,
            'cookie_consent_given_at' => now(),
            'gdpr_ip_address' => $ipAddress,
        ]);
    }

    /**
     * Update data processing consent.
     */
    public function updateDataProcessingConsent(bool $consent, ?string $ipAddress = null): void
    {
        $this->update([
            'data_processing_consent' => $consent,
            'data_processing_consent_given_at' => $consent ? now() : null,
            'gdpr_ip_address' => $ipAddress,
        ]);
    }
}
