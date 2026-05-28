<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case WaitingDocuments = 'waiting_documents';
    case WaitingPayment = 'waiting_payment';
    case WaitingStart = 'waiting_start';
    case Active = 'active';
    case Theory = 'theory';
    case Practice = 'practice';
    case ReadyInternalExam = 'ready_internal_exam';
    case ReadyStateExam = 'ready_state_exam';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expelled = 'expelled';
    case Archived = 'archived';

    public function isActiveWorkflow(): bool
    {
        return in_array($this, [
            self::Pending,
            self::WaitingDocuments,
            self::WaitingPayment,
            self::WaitingStart,
            self::Active,
            self::Theory,
            self::Practice,
            self::ReadyInternalExam,
            self::ReadyStateExam,
        ], true);
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    public function isCancelled(): bool
    {
        return in_array($this, [
            self::Cancelled,
            self::Expelled,
        ], true);
    }
}
