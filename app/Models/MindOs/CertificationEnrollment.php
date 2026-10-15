<?php

namespace App\Models\MindOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationEnrollment extends Model
{
    public const STATUSES = [
        'applied' => ['Pendaftaran', 'warning'],
        'learning' => ['Belajar & Praktik', 'primary'],
        'exam' => ['Ujian', 'info'],
        'certified' => ['Tersertifikasi', 'success'],
        'rejected' => ['Ditolak', 'danger'],
    ];

    protected $fillable = [
        'user_id', 'certification_program_id', 'status', 'hours_done', 'clients_done',
        'motivation', 'certificate_number', 'certified_at', 'admin_notes',
    ];

    protected $casts = ['certified_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(CertificationProgram::class, 'certification_program_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'secondary';
    }
}
