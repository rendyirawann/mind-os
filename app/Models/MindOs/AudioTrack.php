<?php

namespace App\Models\MindOs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AudioTrack extends Model
{
    protected $fillable = ['slug', 'title', 'description', 'stage', 'duration_seconds', 'audio_url', 'script', 'sort_order', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort_order');
    }

    public function getDurationLabelAttribute(): string
    {
        return intdiv((int) $this->duration_seconds, 60) . ' menit';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
