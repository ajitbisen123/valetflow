<?php

namespace Database\Seeders;

use App\Enums\RetrievalStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\GuestSession;
use App\Models\Hotel;
use App\Models\ParkingLocation;
use App\Models\ParkingTicket;
use App\Models\RetrievalRequest;
use App\Models\TicketEvent;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\QrTokenService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $qrService = app(QrTokenService::class);

        // 1. Demo Hotel
        $hotel = Hotel::create([
            'name' => 'Grand Azure Luxury Resort',
            'slug' => 'grand-azure',
            'timezone' => 'UTC',
            'logo_path' => 'logo.jpg',
            'settings' => [
                'target_options' => [5, 10, 15],
                'currency' => 'USD',
                'require_floor' => true,
                'require_slot' => false,
            ],
            'status' => 'active',
        ]);

        // 2. Staff & Driver Accounts
        $password = Hash::make('password');

        $admin = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Alexander Vance (Admin)',
            'email' => 'admin@valetflow.test',
            'password' => $password,
            'role' => UserRole::HotelAdmin,
            'active' => true,
        ]);

        $manager = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Eleanor Brooks (Manager)',
            'email' => 'manager@valetflow.test',
            'password' => $password,
            'role' => UserRole::Manager,
            'active' => true,
        ]);

        $receptionist = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Chloe Bennett (Reception)',
            'email' => 'reception@valetflow.test',
            'password' => $password,
            'role' => UserRole::Receptionist,
            'active' => true,
        ]);

        $dispatcher = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'David Sterling (Dispatcher)',
            'email' => 'dispatcher@valetflow.test',
            'password' => $password,
            'role' => UserRole::Dispatcher,
            'active' => true,
        ]);

        $driver1 = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Marcus Cole (Driver)',
            'email' => 'driver1@valetflow.test',
            'password' => $password,
            'role' => UserRole::Driver,
            'active' => true,
        ]);

        $driver2 = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Liam Reynolds (Driver)',
            'email' => 'driver2@valetflow.test',
            'password' => $password,
            'role' => UserRole::Driver,
            'active' => true,
        ]);

        $driver3 = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Sophia Taylor (Driver)',
            'email' => 'driver3@valetflow.test',
            'password' => $password,
            'role' => UserRole::Driver,
            'active' => true,
        ]);

        // 3. Parking Locations
        $locA1 = ParkingLocation::create([
            'hotel_id' => $hotel->id,
            'zone' => 'A - West Wing',
            'floor' => 'Level 1',
            'slot' => 'A-101',
            'description' => 'Covered executive bays near west elevator',
            'active' => true,
        ]);

        $locA2 = ParkingLocation::create([
            'hotel_id' => $hotel->id,
            'zone' => 'A - West Wing',
            'floor' => 'Level 1',
            'slot' => 'A-102',
            'description' => 'Covered executive bays near west elevator',
            'active' => true,
        ]);

        $locB1 = ParkingLocation::create([
            'hotel_id' => $hotel->id,
            'zone' => 'B - North Deck',
            'floor' => 'Level 2',
            'slot' => 'B-205',
            'description' => 'Spacious SUV and sedan parking',
            'active' => true,
        ]);

        $locVIP = ParkingLocation::create([
            'hotel_id' => $hotel->id,
            'zone' => 'VIP Porte-Cochere',
            'floor' => 'Ground Level',
            'slot' => 'VIP-01',
            'description' => 'Immediate turnaround for VIP and suites',
            'active' => true,
        ]);

        // 4. Sample Vehicles & Tickets

        // Ticket 1: PARKED (Ready to test guest retrieval from guest page)
        $vehicle1 = Vehicle::create([
            'hotel_id' => $hotel->id,
            'registration_number' => 'MH-02-AZ-9988',
            'make' => 'Mercedes-Benz',
            'model' => 'S-Class 500',
            'color' => 'Obsidian Black',
            'notes_private' => 'VIP guest. Key has tracker.',
        ]);

        $guestSession1 = GuestSession::create([
            'hotel_id' => $hotel->id,
            'guest_name' => 'Mr. Rohit Sharma',
            'booking_reference' => 'BK-99201',
            'created_by' => $receptionist->id,
        ]);

        $ticket1 = ParkingTicket::create([
            'hotel_id' => $hotel->id,
            'ticket_number' => 'VF-1001',
            'guest_session_id' => $guestSession1->id,
            'vehicle_id' => $vehicle1->id,
            'assigned_driver_id' => $driver1->id,
            'parking_location_id' => $locA1->id,
            'status' => TicketStatus::Parked,
            'checked_in_at' => now()->subMinutes(120),
            'parked_at' => now()->subMinutes(105),
            'created_by' => $receptionist->id,
        ]);

        $qrService->generateForTicket($ticket1, $receptionist);

        TicketEvent::create([
            'hotel_id' => $hotel->id,
            'ticket_id' => $ticket1->id,
            'actor_user_id' => $receptionist->id,
            'event_type' => 'check_in',
            'old_status' => null,
            'new_status' => TicketStatus::CheckedIn->value,
            'reason' => 'Guest vehicle received at reception',
            'created_at' => now()->subMinutes(120),
        ]);

        TicketEvent::create([
            'hotel_id' => $hotel->id,
            'ticket_id' => $ticket1->id,
            'actor_user_id' => $driver1->id,
            'event_type' => 'status_change',
            'old_status' => TicketStatus::ParkingInProgress->value,
            'new_status' => TicketStatus::Parked->value,
            'reason' => 'Vehicle parked at Zone A - West Wing, Slot A-101',
            'created_at' => now()->subMinutes(105),
        ]);

        // Ticket 2: RETRIEVAL_REQUESTED (Guest has requested retrieval - Driver can accept/retrieve)
        $vehicle2 = Vehicle::create([
            'hotel_id' => $hotel->id,
            'registration_number' => 'DL-01-CX-4521',
            'make' => 'BMW',
            'model' => '740Li',
            'color' => 'Alpine White',
            'notes_private' => 'Prefers valet to wait at main entrance canopy',
        ]);

        $guestSession2 = GuestSession::create([
            'hotel_id' => $hotel->id,
            'guest_name' => 'Ms. Priya Kapoor',
            'booking_reference' => 'BK-88314',
            'created_by' => $receptionist->id,
        ]);

        $ticket2 = ParkingTicket::create([
            'hotel_id' => $hotel->id,
            'ticket_number' => 'VF-1002',
            'guest_session_id' => $guestSession2->id,
            'vehicle_id' => $vehicle2->id,
            'assigned_driver_id' => $driver1->id,
            'parking_location_id' => $locA2->id,
            'status' => TicketStatus::RetrievalRequested,
            'checked_in_at' => now()->subMinutes(90),
            'parked_at' => now()->subMinutes(75),
            'retrieval_requested_at' => now()->subMinutes(5),
            'created_by' => $receptionist->id,
        ]);

        $qr2 = $qrService->generateForTicket($ticket2, $receptionist);

        RetrievalRequest::create([
            'hotel_id' => $hotel->id,
            'ticket_id' => $ticket2->id,
            'requested_by_guest_token_id' => $qr2['qr_token']->id,
            'requested_minutes' => 10,
            'requested_at' => now()->subMinutes(5),
            'target_ready_at' => now()->addMinutes(5),
            'assigned_driver_id' => $driver1->id,
            'status' => RetrievalStatus::Requested,
        ]);

        TicketEvent::create([
            'hotel_id' => $hotel->id,
            'ticket_id' => $ticket2->id,
            'actor_user_id' => null,
            'event_type' => 'retrieval_requested',
            'old_status' => TicketStatus::Parked->value,
            'new_status' => TicketStatus::RetrievalRequested->value,
            'reason' => 'Guest requested car in 10 minutes',
            'created_at' => now()->subMinutes(5),
        ]);

        // Ticket 3: READY_AT_GATE (Vehicle ready at pickup gate, ready for handover)
        $vehicle3 = Vehicle::create([
            'hotel_id' => $hotel->id,
            'registration_number' => 'KA-05-MK-7722',
            'make' => 'Audi',
            'model' => 'Q7 Quattro',
            'color' => 'Daytona Grey',
            'notes_private' => 'Roof rack installed',
        ]);

        $guestSession3 = GuestSession::create([
            'hotel_id' => $hotel->id,
            'guest_name' => 'Dr. Ananya Rao',
            'booking_reference' => 'BK-11045',
            'created_by' => $receptionist->id,
        ]);

        $ticket3 = ParkingTicket::create([
            'hotel_id' => $hotel->id,
            'ticket_number' => 'VF-1003',
            'guest_session_id' => $guestSession3->id,
            'vehicle_id' => $vehicle3->id,
            'assigned_driver_id' => $driver2->id,
            'parking_location_id' => $locB1->id,
            'status' => TicketStatus::ReadyAtGate,
            'checked_in_at' => now()->subMinutes(180),
            'parked_at' => now()->subMinutes(160),
            'retrieval_requested_at' => now()->subMinutes(20),
            'ready_at_gate_at' => now()->subMinutes(3),
            'created_by' => $receptionist->id,
        ]);

        $qrService->generateForTicket($ticket3, $receptionist);

        // Ticket 4: CHECKED_IN (New check-in waiting for driver to park)
        $vehicle4 = Vehicle::create([
            'hotel_id' => $hotel->id,
            'registration_number' => 'HR-26-DK-1123',
            'make' => 'Porsche',
            'model' => 'Panamera GTS',
            'color' => 'Carmine Red',
            'notes_private' => 'Handle with utmost care',
        ]);

        $guestSession4 = GuestSession::create([
            'hotel_id' => $hotel->id,
            'guest_name' => 'Vikram Singhania',
            'booking_reference' => 'BK-55219',
            'created_by' => $receptionist->id,
        ]);

        $ticket4 = ParkingTicket::create([
            'hotel_id' => $hotel->id,
            'ticket_number' => 'VF-1004',
            'guest_session_id' => $guestSession4->id,
            'vehicle_id' => $vehicle4->id,
            'assigned_driver_id' => $driver3->id,
            'parking_location_id' => null,
            'status' => TicketStatus::CheckedIn,
            'checked_in_at' => now()->subMinutes(10),
            'created_by' => $receptionist->id,
        ]);

        $qrService->generateForTicket($ticket4, $receptionist);

        TicketEvent::create([
            'hotel_id' => $hotel->id,
            'ticket_id' => $ticket4->id,
            'actor_user_id' => $receptionist->id,
            'event_type' => 'check_in',
            'old_status' => null,
            'new_status' => TicketStatus::CheckedIn->value,
            'reason' => 'Ticket created at reception counter',
            'created_at' => now()->subMinutes(10),
        ]);
    }
}
