<?php

namespace App\Models\MindOs;

use App\Models\User;
use App\Support\SixR;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SixRLog extends Model
{
    protected $table = 'six_r_logs';

    protected $fillable = ['user_id', 'mind_bug_id', 'stage', 'modality', 'note', 'intensity_before', 'intensity_after'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(MindBug::class, 'mind_bug_id');
    }

    public function getStageInfoAttribute(): array
    {
        return SixR::stage($this->stage);
    }
}
