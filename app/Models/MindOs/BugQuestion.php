<?php

namespace App\Models\MindOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugQuestion extends Model
{
    protected $fillable = ['mind_bug_id', 'question', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function bug(): BelongsTo
    {
        return $this->belongsTo(MindBug::class, 'mind_bug_id');
    }
}
