<?php

namespace App\Services;

use App\Models\ParkingTicket;
use App\Models\QrToken;
use App\Models\User;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrTokenService
{
    /**
     * Generate a new guest ticket QR token.
     *
     * @return array{token: string, qr_token: QrToken}
     */
    public function generateForTicket(ParkingTicket $ticket, ?User $createdBy = null): array
    {
        $rawToken = Str::random(48);
        $tokenHash = hash('sha256', $rawToken);

        $qrToken = QrToken::create([
            'hotel_id' => $ticket->hotel_id,
            'ticket_id' => $ticket->id,
            'token_hash' => $tokenHash,
            'purpose' => 'guest_ticket',
            'created_by' => $createdBy?->id,
        ]);

        return [
            'token' => $rawToken,
            'qr_token' => $qrToken,
        ];
    }

    /**
     * Find and validate a token by raw presentation.
     */
    public function findValidGuestToken(string $rawToken): ?QrToken
    {
        $hash = hash('sha256', $rawToken);

        $token = QrToken::with(['ticket.hotel', 'ticket.vehicle', 'ticket.parkingLocation', 'ticket.assignedDriver', 'ticket.activeRetrievalRequest'])
            ->where('token_hash', $hash)
            ->where('purpose', 'guest_ticket')
            ->whereNull('revoked_at')
            ->first();

        if (! $token || $token->isExpired()) {
            return null;
        }

        $token->update(['last_used_at' => now()]);

        return $token;
    }

    /**
     * Revoke all active tokens for a ticket.
     */
    public function revokeTicketTokens(ParkingTicket $ticket): void
    {
        QrToken::where('ticket_id', $ticket->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Generate SVG QR code for given URL.
     */
    public function generateSvg(string $url, int $size = 220): string
    {
        return QrCode::size($size)->generate($url);
    }
}
