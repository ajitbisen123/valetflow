<?php

namespace App\Models;

use App\Enums\RetrievalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetrievalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'ticket_id',
        'requested_by_guest_token_id',
        'requested_minutes',
        'requested_at',
        'target_ready_at',
        'assigned_driver_id',
        'accepted_at',
        'started_at',
        'ready_at',
        'completed_at',
        'status',
        'cancellation_reason',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'status' => RetrievalStatus::class,
            'requested_minutes' => 'integer',
            'requested_at' => 'datetime',
            'target_ready_at' => 'datetime',
            'accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ParkingTicket::class);
    }

    public function guestToken(): BelongsTo
    {
        return $this->belongsTo(QrToken::class, 'requested_by_guest_token_id');
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, [RetrievalStatus::Completed, RetrievalStatus::Cancelled])) {
            return false;
        }

        return now()->isAfter($this->target_ready_at);
    }
}

