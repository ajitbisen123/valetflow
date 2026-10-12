<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'logo_path',
        'settings',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'driver');
    }

    public function parkingLocations(): HasMany
    {
        return $this->hasMany(ParkingLocation::class);
    }

    public function parkingTickets(): HasMany
    {
        return $this->hasMany(ParkingTicket::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function retrievalRequests(): HasMany
    {
        return $this->hasMany(RetrievalRequest::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function settingsList(): HasMany
    {
        return $this->hasMany(Setting::class);
    }
}

