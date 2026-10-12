<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParkingTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'ticket_number',
        'guest_session_id',
        'vehicle_id',
        'assigned_driver_id',
        'parking_location_id',
        'status',
        'checked_in_at',
        'parked_at',
        'retrieval_requested_at',
        'ready_at_gate_at',
        'handed_over_at',
        'closed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'checked_in_at' => 'datetime',
            'parked_at' => 'datetime',
            'retrieval_requested_at' => 'datetime',
            'ready_at_gate_at' => 'datetime',
            'handed_over_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function guestSession(): BelongsTo
    {
        return $this->belongsTo(GuestSession::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function parkingLocation(): BelongsTo
    {
        return $this->belongsTo(ParkingLocation::class);
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(QrToken::class, 'ticket_id');
    }

    public function activeQrToken(): HasOne
    {
        return $this->hasOne(QrToken::class, 'ticket_id')
            ->whereNull('revoked_at')
            ->where('purpose', 'guest_ticket')
            ->latestOfMany();
    }

    public function retrievalRequests(): HasMany
    {
        return $this->hasMany(RetrievalRequest::class, 'ticket_id');
    }

    public function activeRetrievalRequest(): HasOne
    {
        return $this->hasOne(RetrievalRequest::class, 'ticket_id')
            ->whereIn('status', ['REQUESTED', 'ASSIGNED', 'IN_PROGRESS', 'READY_AT_GATE', 'DELAYED'])
            ->latestOfMany();
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class, 'ticket_id')->orderBy('created_at', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isParked(): bool
    {
        return $this->status === TicketStatus::Parked;
    }

    public function isCheckedIn(): bool
    {
        return $this->status === TicketStatus::CheckedIn;
    }

    public function isParkingInProgress(): bool
    {
        return $this->status === TicketStatus::ParkingInProgress;
    }

    public function isRetrievalRequested(): bool
    {
        return $this->status === TicketStatus::RetrievalRequested;
    }

    public function isRetrievalInProgress(): bool
    {
        return $this->status === TicketStatus::RetrievalInProgress;
    }

    public function isReadyAtGate(): bool
    {
        return $this->status === TicketStatus::ReadyAtGate;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [TicketStatus::HandedOver, TicketStatus::Cancelled]);
    }
}

