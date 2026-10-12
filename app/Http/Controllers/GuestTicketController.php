<?php

namespace App\Http\Controllers;

use App\Services\QrTokenService;
use App\Services\RetrievalService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestTicketController extends Controller
{
    public function __construct(
        protected QrTokenService $qrTokenService,
        protected RetrievalService $retrievalService
    ) {}

    public function show(string $token): View
    {
        $qrToken = $this->qrTokenService->findValidGuestToken($token);

        if (! $qrToken || ! $qrToken->ticket) {
            return view('guest.invalid', ['message' => 'This parking pass is invalid, expired, or has been revoked. Please contact the hotel valet desk.']);
        }

        $ticket = $qrToken->ticket;
        $activeRequest = $ticket->activeRetrievalRequest;

        return view('guest.ticket', [
            'token' => $token,
            'ticket' => $ticket,
            'hotel' => $ticket->hotel,
            'vehicle' => $ticket->vehicle,
            'activeRequest' => $activeRequest,
        ]);
    }

    public function storeRequest(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'requested_minutes' => ['required', 'integer', 'in:5,10,15'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ]);

        $qrToken = $this->qrTokenService->findValidGuestToken($token);

        if (! $qrToken || ! $qrToken->ticket) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Invalid or expired QR token.'], 403);
            }

            return back()->with('error', 'Invalid or expired QR token.');
        }

        try {
            $this->retrievalService->createGuestRequest(
                $qrToken->ticket,
                (int) $validated['requested_minutes'],
                $qrToken,
                $validated['idempotency_key'] ?? null
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('guest.show', $token)->with('success', 'Your vehicle retrieval request has been submitted!');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function status(string $token): JsonResponse
    {
        $qrToken = $this->qrTokenService->findValidGuestToken($token);

        if (! $qrToken || ! $qrToken->ticket) {
            return response()->json(['error' => 'Invalid token'], 404);
        }

        $ticket = $qrToken->ticket;
        $activeRequest = $ticket->activeRetrievalRequest;

        return response()->json([
            'ticket_number' => $ticket->ticket_number,
            'ticket_status' => $ticket->status->value,
            'ticket_status_label' => $ticket->status->label(),
            'badge_classes' => $ticket->status->badgeClasses(),
            'has_active_request' => $activeRequest !== null,
            'request_status' => $activeRequest?->status->value,
            'request_status_label' => $activeRequest?->status->label(),
            'target_ready_at' => $activeRequest?->target_ready_at?->toIso8601String(),
            'is_ready_at_gate' => $ticket->isReadyAtGate(),
            'is_handed_over' => $ticket->status->value === 'HANDED_OVER',
            'last_updated' => $ticket->updated_at->toIso8601String(),
        ]);
    }
}
