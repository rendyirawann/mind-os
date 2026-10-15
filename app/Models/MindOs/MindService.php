<?php

namespace App\Models\MindOs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MindService extends Model
{
    public const MODALITIES = [
        'online' => 'Online Live',
        'offline' => 'Tatap Muka',
    ];

    protected $fillable = ['slug', 'modality', 'name', 'stage', 'description', 'price', 'duration_minutes', 'capacity', 'is_group', 'is_active', 'sort_order'];

    protected $casts = ['is_group' => 'boolean', 'is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getPriceLabelAttribute(): string
    {
        return $this->price > 0 ? 'Rp ' . number_format($this->price, 0, ',', '.') : 'Gratis';
    }

    public function getModalityLabelAttribute(): string
    {
        return self::MODALITIES[$this->modality] ?? $this->modality;
    }
}
