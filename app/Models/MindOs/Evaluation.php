<?php

namespace App\Models\MindOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    public const NEXT_STEPS = [
        'self_mastery' => 'Lanjut Self-Mastery / Alumni',
        'konsultasi' => 'Konsultasi Ulang / Ganti Pendekatan',
        'sertifikasi' => 'Minat Sertifikasi Practitioner / Master',
        'komunitas' => 'Tetap di Komunitas',
    ];

    protected $fillable = ['user_id', 'action_plan_id', 'score_before', 'score_after', 'result', 'next_step', 'reflection'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ActionPlan::class, 'action_plan_id');
    }

    public function getNextStepLabelAttribute(): string
    {
        return self::NEXT_STEPS[$this->next_step] ?? $this->next_step;
    }
}
