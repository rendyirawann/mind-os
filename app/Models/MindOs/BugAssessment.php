<?php

namespace App\Models\MindOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugAssessment extends Model
{
    public const RECOMMENDATIONS = [
        'web_app' => 'Lanjut Web App (Self-Healing)',
        'online_live' => 'Booking Online Live',
        'tatap_muka' => 'Booking Tatap Muka',
    ];

    protected $fillable = ['user_id', 'dominant_bug_id', 'total_score', 'percentage', 'level', 'recommendation', 'scores'];

    protected $casts = ['scores' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dominantBug(): BelongsTo
    {
        return $this->belongsTo(MindBug::class, 'dominant_bug_id');
    }

    public function getRecommendationLabelAttribute(): string
    {
        return self::RECOMMENDATIONS[$this->recommendation] ?? $this->recommendation;
    }
}
