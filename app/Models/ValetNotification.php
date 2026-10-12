<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValetNotification extends Model
{
    use HasFactory;

    protected $table = 'valet_notifications';

    protected $fillable = [
        'hotel_id',
        'user_id',
        'retrieval_request_id',
        'channel',
        'message_type',
        'payload',
        'status',
        'attempts',
        'sent_at',
        'error_summary',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'sent_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function retrievalRequest(): BelongsTo
    {
        return $this->belongsTo(RetrievalRequest::class);
    }
}

