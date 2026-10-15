<?php

namespace App\Models\MindOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    protected $fillable = ['user_id', 'mind_bug_id', 'title', 'situation', 'thought', 'feeling', 'body_response', 'intensity', 'insight'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(MindBug::class, 'mind_bug_id');
    }
}
