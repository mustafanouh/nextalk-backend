<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'type',
    ];

    // ── Relationships ────────────────────────────────────────

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Find an existing private (one-to-one) conversation between two users,
     * if one exists. Used to prevent duplicate private conversations.
     */
    public static function findPrivateBetween(int $userIdA, int $userIdB): ?self
    {
        return self::query()
            ->where('type', 'private')
            ->whereHas('participants', fn ($q) => $q->where('users.id', $userIdA))
            ->whereHas('participants', fn ($q) => $q->where('users.id', $userIdB))
            ->withCount('participants')
            ->having('participants_count', '=', 2)
            ->first();
    }
}
