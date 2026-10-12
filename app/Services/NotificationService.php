<?php

namespace App\Services;

use App\Models\ValetNotification;

class NotificationService
{
    /**
     * Store and dispatch an in-app notification.
     *
     * @param  array<string, mixed>  $payload
     */
    public function send(
        string $hotelId,
        string $messageType,
        array $payload,
        ?int $userId = null,
        ?int $requestId = null,
        string $channel = 'in_app'
    ): ValetNotification {
        return ValetNotification::create([
            'hotel_id' => $hotelId,
            'user_id' => $userId,
            'retrieval_request_id' => $requestId,
            'channel' => $channel,
            'message_type' => $messageType,
            'payload' => $payload,
            'status' => 'delivered',
            'attempts' => 1,
            'sent_at' => now(),
        ]);
    }
}

