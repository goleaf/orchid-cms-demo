<?php

namespace App\Enums;

use App\Models\LeadStatus as LeadStatusDictionary;

enum LeadStatus: string
{
    case New = 'new';
    case NoAnswer = 'no_answer';
    case Contacted = 'contacted';
    case ConsultationDone = 'consultation_done';
    case WaitingDocuments = 'waiting_documents';
    case WaitingPayment = 'waiting_payment';
    case AssignedToGroup = 'assigned_to_group';
    case BecameStudent = 'became_student';
    case Rejected = 'rejected';
    case Duplicate = 'duplicate';
    case Spam = 'spam';
    case Archived = 'archived';

    public function label(): string
    {
        return LeadStatusDictionary::translatedLabel($this->value);
    }

    /**
     * @return array<int, string>
     */
    public static function openPipelineValues(): array
    {
        return [
            self::New->value,
            self::NoAnswer->value,
            self::Contacted->value,
            self::ConsultationDone->value,
            self::WaitingDocuments->value,
            self::WaitingPayment->value,
            self::AssignedToGroup->value,
        ];
    }
}
