<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case HotelAdmin = 'hotel_admin';
    case Manager = 'manager';
    case Receptionist = 'receptionist';
    case Dispatcher = 'dispatcher';
    case Driver = 'driver';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Platform Super Admin',
            self::HotelAdmin => 'Hotel Admin',
            self::Manager => 'Hotel Manager',
            self::Receptionist => 'Receptionist',
            self::Dispatcher => 'Dispatcher',
            self::Driver => 'Driver',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::HotelAdmin,
            self::Manager,
            self::Receptionist,
            self::Dispatcher,
            self::Driver,
        ]);
    }

    public function canManageStaff(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::HotelAdmin,
        ]);
    }

    public function canManageLocations(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::HotelAdmin,
            self::Manager,
        ]);
    }

    public function canCreateTicket(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::HotelAdmin,
            self::Manager,
            self::Receptionist,
        ]);
    }

    public function canDispatch(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::HotelAdmin,
            self::Manager,
            self::Dispatcher,
        ]);
    }
}

