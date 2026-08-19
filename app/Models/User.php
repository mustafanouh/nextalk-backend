<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasApiTokens, HasFactory, InteractsWithMedia, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Computed on every serialization — this is what fixes the "avatar
    // never actually reaches the frontend" gap (see backend update plan
    // item #2 / #6). The raw `avatar` DB column is gone; Spatie's `media`
    // table is now the single source of truth for the file itself.
    protected $appends = ['avatar_url', 'avatar_thumb_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'caller_id');
    }

    // ── Route model binding by username ─────────────────────
    // Allows GET /api/users/{username} to resolve directly.
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    // ── Media (avatar) ───────────────────────────────────────

    /**
     * Single-file collection: Spatie automatically deletes the previous
     * file (DB row + disk object) whenever a new one is added to a
     * singleFile() collection, which is exactly the "orphaned avatar"
     * leak this replaces (see backend update plan item #6).
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk(config('filesystems.default'));
    }

    /**
     * Generates a 256x256 thumbnail alongside the original — the API
     * returns both via avatar_url (original) and avatar_thumb_url.
     * ->nonQueued() runs it inline during the request (simplest for a
     * scaffold); switch to ->queued() once volume justifies moving it
     * to a queue worker.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 256, 256)
            ->nonQueued(); // small enough to do inline; flip to queued() if this gets slow
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: null;
    }

    public function getAvatarThumbUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb') ?: null;
    }

    // ── Custom notifications ─────────────────────────────────
    // Overrides the traits' defaults (AuthenticatableTrait /
    // Illuminate\Auth\Passwords\CanResetPassword), which build links via
    // route('verification.verify', ...) / route('password.reset', ...).
    // This project registers 'verification.verify' as an API route (see
    // routes/api.php), but never registers 'password.reset' at all — the
    // frontend's own /reset-password page is the destination instead
    // (see backend update plan #1 for why the defaults crash outright).

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
