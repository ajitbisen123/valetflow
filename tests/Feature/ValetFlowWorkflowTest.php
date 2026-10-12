<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\ParkingLocation;
use App\Models\ParkingTicket;
use App\Models\RetrievalRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\QrTokenService;
use App\Services\RetrievalService;
use App\Services\TicketWorkflowService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ValetFlowWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected User $admin;

    protected User $receptionist;

    protected User $driver;

    protected ParkingLocation $location;

    protected QrTokenService $qrService;

    protected TicketWorkflowService $workflowService;

    protected RetrievalService $retrievalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Grand Test Hotel',
            'slug' => 'grand-test',
            'timezone' => 'UTC',
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Admin User',
            'email' => 'admin@test.test',
            'password' => bcrypt('password'),
            'role' => UserRole::HotelAdmin,
            'active' => true,
        ]);

        $this->receptionist = User::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Reception User',
            'email' => 'reception@test.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Receptionist,
            'active' => true,
        ]);

        $this->driver = User::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Driver User',
            'email' => 'driver@test.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Driver,
            'active' => true,
        ]);

        $this->location = ParkingLocation::create([
            'hotel_id' => $this->hotel->id,
            'zone' => 'Zone A',
            'floor' => 'Floor 1',
            'slot' => 'A-101',
            'active' => true,
        ]);

        $this->qrService = app(QrTokenService::class);
        $this->workflowService = app(TicketWorkflowService::class);
        $this->retrievalService = app(RetrievalService::class);
    }

    public function test_guest_can_open_valid_active_qr_and_see_only_safe_fields(): void
    {
        $vehicle = Vehicle::create([
            'hotel_id' => $this->hotel->id,
            'registration_number' => 'MH-01-AB-1234',
            'make' => 'BMW',
            'notes_private' => 'VIP secret notes that must not leak',
        ]);

        $ticket = ParkingTicket::create([
            'hotel_id' => $this->hotel->id,
            'ticket_number' => 'VF-9001',
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::Parked,
            'checked_in_at' => now(),
            'parking_location_id' => $this->location->id,
        ]);

        $qrData = $this->qrService->generateForTicket($ticket);
        $token = $qrData['token'];

        $response = $this->get(route('guest.show', $token));

        $response->assertStatus(200);
        $response->assertSee('VF-9001');
        $response->assertSee('MH-01-AB-1234');
        $response->assertSee('BMW');
        $response->assertDontSee('VIP secret notes that must not leak');
    }

    public function test_retrieval_is_blocked_until_ticket_is_parked(): void
    {
        $vehicle = Vehicle::create([
            'hotel_id' => $this->hotel->id,
            'registration_number' => 'MH-01-AB-1234',
        ]);

        $ticket = ParkingTicket::create([
            'hotel_id' => $this->hotel->id,
            'ticket_number' => 'VF-9002',
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::CheckedIn, // Not yet parked!
            'checked_in_at' => now(),
        ]);

        $qrData = $this->qrService->generateForTicket($ticket);

        $this->expectException(DomainException::class);
        $this->retrievalService->createGuestRequest($ticket, 10, $qrData['qr_token']);
    }

    public function test_only_5_10_and_15_are_accepted_as_target_minutes(): void
    {
        $vehicle = Vehicle::create([
            'hotel_id' => $this->hotel->id,
            'registration_number' => 'MH-01-AB-1234',
        ]);

        $ticket = ParkingTicket::create([
            'hotel_id' => $this->hotel->id,
            'ticket_number' => 'VF-9003',
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::Parked,
            'checked_in_at' => now(),
            'parking_location_id' => $this->location->id,
        ]);

        $qrData = $this->qrService->generateForTicket($ticket);

        $this->expectException(InvalidArgumentException::class);
        $this->retrievalService->createGuestRequest($ticket, 25, $qrData['qr_token']);
    }

    public function test_prevent_duplicate_active_retrieval_requests(): void
    {
        $vehicle = Vehicle::create([
            'hotel_id' => $this->hotel->id,
            'registration_number' => 'MH-01-AB-1234',
        ]);

        $ticket = ParkingTicket::create([
            'hotel_id' => $this->hotel->id,
            'ticket_number' => 'VF-9004',
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::Parked,
            'checked_in_at' => now(),
            'parking_location_id' => $this->location->id,
        ]);

        $qrData = $this->qrService->generateForTicket($ticket);

        $req1 = $this->retrievalService->createGuestRequest($ticket, 10, $qrData['qr_token'], 'idempotent-key-1');
        $req2 = $this->retrievalService->createGuestRequest($ticket, 10, $qrData['qr_token'], 'idempotent-key-1');

        $this->assertEquals($req1->id, $req2->id);
        $this->assertEquals(1, RetrievalRequest::where('ticket_id', $ticket->id)->count());
    }

    public function test_invalid_status_transitions_are_rejected(): void
    {
        $vehicle = Vehicle::create([
            'hotel_id' => $this->hotel->id,
            'registration_number' => 'MH-01-AB-1234',
        ]);

        $ticket = ParkingTicket::create([
            'hotel_id' => $this->hotel->id,
            'ticket_number' => 'VF-9005',
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::CheckedIn,
            'checked_in_at' => now(),
        ]);

        // Attempt direct jump from CHECKED_IN to HANDED_OVER (illegal)
        $this->expectException(DomainException::class);
        $this->workflowService->transition($ticket, TicketStatus::HandedOver, $this->admin);
    }

    public function test_successful_status_change_creates_ticket_event_with_actor_and_timestamp(): void
    {
        $vehicle = Vehicle::create([
            'hotel_id' => $this->hotel->id,
            'registration_number' => 'MH-01-AB-1234',
        ]);

        $ticket = ParkingTicket::create([
            'hotel_id' => $this->hotel->id,
            'ticket_number' => 'VF-9006',
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::CheckedIn,
            'checked_in_at' => now(),
        ]);

        $this->workflowService->startParking($ticket, $this->driver);

        $this->assertEquals(TicketStatus::ParkingInProgress, $ticket->fresh()->status);

        $this->assertDatabaseHas('ticket_events', [
            'ticket_id' => $ticket->id,
            'actor_user_id' => $this->driver->id,
            'old_status' => TicketStatus::CheckedIn->value,
            'new_status' => TicketStatus::ParkingInProgress->value,
        ]);
    }

    public function test_qr_revocation_prevents_further_access(): void
    {
        $vehicle = Vehicle::create([
            'hotel_id' => $this->hotel->id,
            'registration_number' => 'MH-01-AB-1234',
        ]);

        $ticket = ParkingTicket::create([
            'hotel_id' => $this->hotel->id,
            'ticket_number' => 'VF-9007',
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::Parked,
            'checked_in_at' => now(),
            'parking_location_id' => $this->location->id,
        ]);

        $qrData = $this->qrService->generateForTicket($ticket);
        $token = $qrData['token'];

        // Revoke token
        $this->qrService->revokeTicketTokens($ticket);

        $response = $this->get(route('guest.show', $token));
        $response->assertSee('Invalid or Expired Valet Pass');
    }

    public function test_parking_location_is_required_before_mark_as_parked(): void
    {
        $vehicle = Vehicle::create([
            'hotel_id' => $this->hotel->id,
            'registration_number' => 'MH-01-AB-1234',
        ]);

        $ticket = ParkingTicket::create([
            'hotel_id' => $this->hotel->id,
            'ticket_number' => 'VF-9008',
            'vehicle_id' => $vehicle->id,
            'status' => TicketStatus::ParkingInProgress,
            'checked_in_at' => now(),
            'parking_location_id' => null,
        ]);

        $this->expectException(DomainException::class);
        $this->workflowService->transition($ticket, TicketStatus::Parked, $this->driver);
    }
}
