<?php

namespace App\Models\MindOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const STATUSES = [
        'pending' => ['Menunggu', 'warning'],
        'confirmed' => ['Terkonfirmasi', 'primary'],
        'completed' => ['Selesai', 'success'],
        'cancelled' => ['Dibatalkan', 'danger'],
    ];

    protected $fillable = [
        'code', 'user_id', 'mind_service_id', 'practitioner_id', 'scheduled_at', 'status',
        'payment_status', 'amount', 'complaint', 'meeting_link', 'location', 'practitioner_notes',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->code ??= 'MOS-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MindService::class, 'mind_service_id');
    }

    public function practitioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'practitioner_id');
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
