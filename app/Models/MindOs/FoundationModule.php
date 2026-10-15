<?php

namespace App\Models\MindOs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FoundationModule extends Model
{
    public const CATEGORIES = [
        'filosofi' => 'Filosofi Dasar',
        'lapisan' => '5 Lapisan Diri',
        'bug' => '7 Pola Pikiran',
    ];

    protected $fillable = ['slug', 'category', 'title', 'summary', 'content', 'duration_minutes', 'sort_order', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort_order');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
