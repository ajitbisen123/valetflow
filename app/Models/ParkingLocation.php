<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'zone',
        'floor',
        'slot',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(ParkingTicket::class);
    }

    public function getFormattedLocationAttribute(): string
    {
        $parts = ["Zone {$this->zone}"];
        if (! empty($this->floor)) {
            $parts[] = "Floor {$this->floor}";
        }
        if (! empty($this->slot)) {
            $parts[] = "Slot {$this->slot}";
        }

        return implode(' - ', $parts);
    }
}

