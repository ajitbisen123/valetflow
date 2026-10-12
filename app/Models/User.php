<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'hotel_id',
        'name',
        'email',
        'password',
        'role',
        'active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(ParkingTicket::class, 'assigned_driver_id');
    }

    public function assignedRequests(): HasMany
    {
        return $this->hasMany(RetrievalRequest::class, 'assigned_driver_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isHotelAdmin(): bool
    {
        return $this->role === UserRole::HotelAdmin;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isReceptionist(): bool
    {
        return $this->role === UserRole::Receptionist;
    }

    public function isDispatcher(): bool
    {
        return $this->role === UserRole::Dispatcher;
    }

    public function isDriver(): bool
    {
        return $this->role === UserRole::Driver;
    }

    public function canAccessAdminPanel(): bool
    {
        return in_array($this->role, [
            UserRole::SuperAdmin,
            UserRole::HotelAdmin,
            UserRole::Manager,
            UserRole::Receptionist,
            UserRole::Dispatcher,
        ]);
    }
}
