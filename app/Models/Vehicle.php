<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'registration_number',
        'make',
        'model',
        'color',
        'notes_private',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(ParkingTicket::class);
    }

    public function getFormattedVehicleAttribute(): string
    {
        $parts = array_filter([$this->color, $this->make, $this->model]);

        return empty($parts) ? $this->registration_number : $this->registration_number.' ('.implode(' ', $parts).')';
    }
}
