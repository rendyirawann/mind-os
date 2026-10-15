<?php

namespace App\Models\MindOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionPlanDay extends Model
{
    public $timestamps = false;

    protected $fillable = ['action_plan_id', 'day_number', 'task', 'done_at', 'reflection'];

    protected $casts = ['done_at' => 'datetime'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ActionPlan::class, 'action_plan_id');
    }
}
