<?php

namespace App\Models\MindOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    public const CATEGORIES = [
        'cerita' => 'Cerita Perjalanan',
        'tanya' => 'Tanya Jawab',
        'insight' => 'Insight',
        'kemenangan' => 'Kemenangan Kecil',
    ];

    protected $fillable = ['user_id', 'category', 'title', 'body', 'is_pinned', 'is_anonymous'];

    protected $casts = ['is_pinned' => 'boolean', 'is_anonymous' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class)->oldest();
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->is_anonymous ? 'Anggota Anonim' : ($this->user->name ?? 'Anggota');
    }
}
