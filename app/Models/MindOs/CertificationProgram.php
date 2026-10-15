<?php

namespace App\Models\MindOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificationProgram extends Model
{
    public const LEVELS = [
        'practitioner' => 'Mind OS Practitioner',
        'master' => 'Mind OS Master Practitioner',
        'trainer' => 'Mind OS Trainer & Lisensi',
    ];

    protected $fillable = ['slug', 'level', 'name', 'prerequisite', 'hours', 'practice_clients', 'curriculum', 'description', 'price', 'sort_order'];

    protected $casts = ['curriculum' => 'array'];

    public function enrollments(): HasMany
    {
        return $this->hasMany(CertificationEnrollment::class);
    }

    public function getPriceLabelAttribute(): string
    {
        return $this->price > 0 ? 'Rp ' . number_format($this->price, 0, ',', '.') : 'Hubungi kami';
    }
}
