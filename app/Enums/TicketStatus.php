<?php

namespace App\Enums;

enum TicketStatus: string
{
    case CheckedIn = 'CHECKED_IN';
    case ParkingInProgress = 'PARKING_IN_PROGRESS';
    case Parked = 'PARKED';
    case RetrievalRequested = 'RETRIEVAL_REQUESTED';
    case RetrievalInProgress = 'RETRIEVAL_IN_PROGRESS';
    case ReadyAtGate = 'READY_AT_GATE';
    case HandedOver = 'HANDED_OVER';
    case ParkingIssue = 'PARKING_ISSUE';
    case OnHold = 'ON_HOLD';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::CheckedIn => 'Checked In',
            self::ParkingInProgress => 'Parking in Progress',
            self::Parked => 'Parked',
            self::RetrievalRequested => 'Retrieval Requested',
            self::RetrievalInProgress => 'Retrieval in Progress',
            self::ReadyAtGate => 'Ready at Gate',
            self::HandedOver => 'Handed Over',
            self::ParkingIssue => 'Parking Issue',
            self::OnHold => 'On Hold',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::CheckedIn => 'bg-blue-100 text-blue-800 border-blue-200',
            self::ParkingInProgress => 'bg-amber-100 text-amber-800 border-amber-200',
            self::Parked => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::RetrievalRequested => 'bg-purple-100 text-purple-800 border-purple-200',
            self::RetrievalInProgress => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::ReadyAtGate => 'bg-teal-100 text-teal-800 border-teal-200',
            self::HandedOver => 'bg-slate-100 text-slate-800 border-slate-200',
            self::ParkingIssue => 'bg-rose-100 text-rose-800 border-rose-200',
            self::OnHold => 'bg-orange-100 text-orange-800 border-orange-200',
            self::Cancelled => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }
}

