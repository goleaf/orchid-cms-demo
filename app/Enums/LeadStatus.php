<?php

namespace App\Enums;

use App\Models\LeadStatus as LeadStatusDictionary;

enum LeadStatus: string
{
    case New = 'new';
    case NoAnswer = 'no_answer';
    case Contacted = 'contacted';
    case Consultation = 'consultation';
    case ConsultationDone = 'consultation_done';
    case WaitingDocuments = 'waiting_documents';
    case WaitingPayment = 'waiting_payment';
    case ReadyToEnroll = 'ready_to_enroll';
    case Enrolled = 'enrolled';
    case AssignedToGroup = 'assigned_to_group';
    case BecameStudent = 'became_student';
    case Lost = 'lost';
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
            self::Consultation->value,
            self::ConsultationDone->value,
            self::WaitingDocuments->value,
            self::WaitingPayment->value,
            self::ReadyToEnroll->value,
            self::AssignedToGroup->value,
        ];
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::Enrolled,
            self::BecameStudent,
            self::Lost,
            self::Rejected,
            self::Duplicate,
            self::Spam,
            self::Archived,
        ], true);
    }

    public function isLost(): bool
    {
        return in_array($this, [
            self::Lost,
            self::Rejected,
            self::Duplicate,
            self::Spam,
        ], true);
    }

    public function isSuccess(): bool
    {
        return in_array($this, [
            self::Enrolled,
            self::BecameStudent,
        ], true);
    }
}
