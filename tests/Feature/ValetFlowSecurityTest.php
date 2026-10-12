<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\ParkingTicket;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValetFlowSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_cannot_access_another_hotels_ticket(): void
    {
        $hotel1 = Hotel::create(['name' => 'Hotel 1', 'slug' => 'h1', 'status' => 'active']);
        $hotel2 = Hotel::create(['name' => 'Hotel 2', 'slug' => 'h2', 'status' => 'active']);

        $driverHotel1 = User::create([
            'hotel_id' => $hotel1->id,
            'name' => 'Driver 1',
            'email' => 'driver1@h1.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Driver,
            'active' => true,
        ]);

        $vehicle2 = Vehicle::create(['hotel_id' => $hotel2->id, 'registration_number' => 'REG-999']);
        $ticketHotel2 = ParkingTicket::create([
            'hotel_id' => $hotel2->id,
            'ticket_number' => 'H2-101',
            'vehicle_id' => $vehicle2->id,
            'status' => TicketStatus::CheckedIn,
            'checked_in_at' => now(),
        ]);

        // Driver from Hotel 1 attempts to access Ticket from Hotel 2
        $response = $this->actingAs($driverHotel1)->get(route('driver.tickets.show', $ticketHotel2->id));

        $response->assertStatus(403);
    }

    public function test_driver_role_cannot_access_admin_dashboard(): void
    {
        $hotel = Hotel::create(['name' => 'Hotel 1', 'slug' => 'h1', 'status' => 'active']);
        $driver = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Driver 1',
            'email' => 'driver@h1.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Driver,
            'active' => true,
        ]);

        $response = $this->actingAs($driver)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $response2 = $this->get(route('driver.dashboard'));
        $response2->assertRedirect(route('login'));
    }
}
