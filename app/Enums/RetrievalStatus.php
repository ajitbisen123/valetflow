<?php

namespace App\Enums;

enum RetrievalStatus: string
{
    case Requested = 'REQUESTED';
    case Assigned = 'ASSIGNED';
    case InProgress = 'IN_PROGRESS';
    case ReadyAtGate = 'READY_AT_GATE';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
    case Delayed = 'DELAYED';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Assigned => 'Driver Assigned',
            self::InProgress => 'Retrieval in Progress',
            self::ReadyAtGate => 'Ready at Gate',
            self::Completed => 'Handed Over',
            self::Cancelled => 'Cancelled',
            self::Delayed => 'Delayed',
        };
    }
}

