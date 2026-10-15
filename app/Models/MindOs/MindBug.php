<?php

namespace App\Models\MindOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class MindBug extends Model
{
    protected $fillable = ['code', 'name', 'tagline', 'description', 'symptoms', 'reframe_hint', 'color', 'sort_order'];

    public function questions(): HasMany
    {
        return $this->hasMany(BugQuestion::class)->orderBy('sort_order');
    }

    /** The seven bugs rarely change — keep them out of the hot path. */
    public static function cachedAll()
    {
        return Cache::remember('mindos.bugs', 3600, fn () => static::orderBy('sort_order')->get());
    }

    public static function flushCache(): void
    {
        Cache::forget('mindos.bugs');
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }
}
