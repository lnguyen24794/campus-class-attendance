<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'course_section_id',
        'period_id',
        'opened_by',
        'title',
        'status',
        'qr_token',
        'qr_expires_at',
        'opens_at',
        'closes_at',
        'late_after_minutes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SessionStatus::class,
            'qr_expires_at' => 'datetime',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'late_after_minutes' => 'integer',
        ];
    }

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isOpen(): bool
    {
        return $this->status === SessionStatus::Open
            && now()->between($this->opens_at, $this->closes_at);
    }

    public function isQrExpired(): bool
    {
        return now()->greaterThan($this->qr_expires_at);
    }
}
