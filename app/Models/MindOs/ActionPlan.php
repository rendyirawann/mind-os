<?php

namespace App\Models\MindOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActionPlan extends Model
{
    public const DAYS = 30;

    protected $fillable = ['user_id', 'mind_bug_id', 'title', 'goal', 'start_date', 'status'];

    protected $casts = ['start_date' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(MindBug::class, 'mind_bug_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(ActionPlanDay::class)->orderBy('day_number');
    }

    /** Day number (1-30) the plan is on today. */
    public function getCurrentDayAttribute(): int
    {
        return max(1, min(self::DAYS, (int) $this->start_date->diffInDays(today()) + 1));
    }
}
