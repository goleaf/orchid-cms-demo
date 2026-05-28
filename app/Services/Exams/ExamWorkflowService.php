<?php

namespace App\Services\Exams;

use App\Actions\CreateOrUpdateExamAdmissionAction;
use App\Actions\RecordExamActivityAction;
use App\Enums\DocumentStatus;
use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus as LegacyExamAttemptStatus;
use App\Enums\ExamChecklistItemStatus;
use App\Enums\ExamParticipantStatus;
use App\Enums\ExamRetakeStatus;
use App\Enums\ExamSessionStatus as LegacyExamSessionStatus;
use App\Enums\ExamType as LegacyExamType;
use App\Enums\PaymentStatus;
use App\Models\ExamActivity;
use App\Models\ExamAdmission;
use App\Models\ExamAdmissionRule;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptStatus as ExamAttemptStatusModel;
use App\Models\ExamChecklistItem;
use App\Models\ExamParticipant;
use App\Models\ExamResult;
use App\Models\ExamResultStatus;
use App\Models\ExamRetake;
use App\Models\ExamSession;
use App\Models\ExamStatus;
use App\Models\ExamType;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamWorkflowService
{
    /**
     * @var array<string, array<int, string>>
     */
    private const SESSION_TRANSITIONS = [
        'draft' => ['scheduled', 'cancelled', 'archived'],
        'scheduled' => ['open', 'in_progress', 'cancelled', 'archived'],
        'open' => ['in_progress', 'completed', 'cancelled', 'archived'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => ['archived'],
        'cancelled' => ['archived'],
        'archived' => [],
    ];

    public function generateExamNumber(mixed $scheduledAt = null): string
    {
        $date = $scheduledAt === null ? now() : Carbon::parse($scheduledAt);
        $prefix = 'EXM-'.$date->format('Ymd').'-';
        $latest = ExamSession::query()
            ->where('exam_number', 'like', $prefix.'%')
            ->orderByDesc('exam_number')
            ->value('exam_number');

        $sequence = 1;
        if (is_string($latest) && preg_match('/(\d+)$/', $latest, $matches) === 1) {
            $sequence = ((int) $matches[1]) + 1;
        }

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (ExamSession::query()->where('exam_number', $number)->exists());

        return $number;
    }

    public function generateAttemptNumber(StudentEnrollment $enrollment, ExamType|int|string|null $type = null): int
    {
        $query = ExamAttempt::query()->where('enrollment_id', $enrollment->id);

        if ($type !== null) {
            $query->where('exam_type', $this->legacyExamType($this->examType($type))->value);
        }

        $attemptNo = (int) (clone $query)->max('attempt_no');
        $attemptNumber = (int) (clone $query)->max('attempt_number');

        return max($attemptNo, $attemptNumber) + 1;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSession(array $data, ?User $user = null): ExamSession
    {
        return DB::transaction(function () use ($data, $user): ExamSession {
            $type = $this->examType($data['type_id'] ?? $data['exam_type_id'] ?? $data['type'] ?? $data['exam_type'] ?? 'internal_theory');
            $status = $this->examStatus($data['status_id'] ?? $data['status'] ?? 'scheduled');
            $scheduledAt = $data['scheduled_at'] ?? $data['starts_at'] ?? now();
            $capacity = (int) ($data['capacity'] ?? 1);
            $seatsTaken = (int) ($data['seats_taken'] ?? 0);

            $this->ensureCapacityIsNotBelowTakenSeats($capacity, $seatsTaken);

            $session = ExamSession::query()->create([
                'exam_number' => $data['exam_number'] ?? $this->generateExamNumber($scheduledAt),
                'type_id' => $type->id,
                'status_id' => $status->id,
                'branch_id' => $data['branch_id'] ?? null,
                'group_id' => $data['group_id'] ?? $data['training_group_id'] ?? null,
                'training_program_id' => $data['training_program_id'] ?? null,
                'training_group_id' => $data['training_group_id'] ?? $data['group_id'] ?? null,
                'instructor_id' => $data['instructor_id'] ?? null,
                'examiner_id' => $data['examiner_id'] ?? $user?->id,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'classroom_id' => $data['classroom_id'] ?? null,
                'exam_type' => $this->legacyExamType($type),
                'provider' => $type->is_official ? 'state' : 'internal',
                'status' => $this->legacySessionStatus($status->code),
                'scheduled_at' => $scheduledAt,
                'starts_at' => $scheduledAt,
                'ends_at' => $data['ends_at'] ?? null,
                'location' => $data['location'] ?? null,
                'capacity' => $capacity,
                'seats_taken' => $seatsTaken,
                'external_reference' => $data['external_reference'] ?? null,
                'official_placeholder_payload' => $data['official_placeholder_payload'] ?? null,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by_id' => $data['created_by_id'] ?? $user?->id,
                'updated_by_id' => $data['updated_by_id'] ?? $user?->id,
            ]);

            $this->activity([
                'exam_session_id' => $session->id,
                'type' => 'session_created',
                'new_value' => $status->code,
                'meta' => ['exam_type' => $type->code],
            ], $user);

            return $session->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSession(ExamSession $session, array $data, ?User $user = null): ExamSession
    {
        return DB::transaction(function () use ($session, $data, $user): ExamSession {
            $oldStatus = $this->sessionStatusCode($session);

            if (array_key_exists('type_id', $data) || array_key_exists('exam_type', $data) || array_key_exists('type', $data)) {
                $type = $this->examType($data['type_id'] ?? $data['type'] ?? $data['exam_type']);
                $data['type_id'] = $type->id;
                $data['exam_type'] = $this->legacyExamType($type);
                $data['provider'] = $type->is_official ? 'state' : 'internal';
            }

            if (array_key_exists('status_id', $data) || array_key_exists('status', $data)) {
                $status = $this->examStatus($data['status_id'] ?? $data['status']);
                $data['status_id'] = $status->id;
                $data['status'] = $this->legacySessionStatus($status->code);
            }

            if (array_key_exists('scheduled_at', $data) && ! array_key_exists('starts_at', $data)) {
                $data['starts_at'] = $data['scheduled_at'];
            }

            if (array_key_exists('starts_at', $data) && ! array_key_exists('scheduled_at', $data)) {
                $data['scheduled_at'] = $data['starts_at'];
            }

            if (array_key_exists('group_id', $data) && ! array_key_exists('training_group_id', $data)) {
                $data['training_group_id'] = $data['group_id'];
            }

            if (array_key_exists('training_group_id', $data) && ! array_key_exists('group_id', $data)) {
                $data['group_id'] = $data['training_group_id'];
            }

            if (array_key_exists('capacity', $data)) {
                $this->ensureCapacityIsNotBelowTakenSeats((int) $data['capacity'], (int) $session->seats_taken);
            }

            $data['updated_by_id'] = $data['updated_by_id'] ?? $user?->id;
            $session->fill($data)->save();

            $newStatus = $this->sessionStatusCode($session->refresh());
            if ($oldStatus !== $newStatus) {
                $this->activity([
                    'exam_session_id' => $session->id,
                    'type' => 'session_status_changed',
                    'old_value' => $oldStatus,
                    'new_value' => $newStatus,
                ], $user);
            } else {
                $this->activity([
                    'exam_session_id' => $session->id,
                    'type' => 'session_updated',
                ], $user);
            }

            return $session->refresh();
        });
    }

    public function changeSessionStatus(ExamSession $session, ExamStatus|int|string $status, ?User $user = null, bool $allowOverride = false): ExamSession
    {
        $target = $this->examStatus($status);
        $old = $this->sessionStatusCode($session);

        if (! $this->canTransitionSessionStatus($session, $target, $allowOverride)) {
            throw ValidationException::withMessages([
                'status_id' => tkey('exams.validation.invalid_session_status_transition'),
            ]);
        }

        $updatedSession = $this->updateSession($session, [
            'status_id' => $target->id,
            'status' => $target->code,
            'internal_notes' => $session->internal_notes,
        ], $user);

        if ($old !== $target->code) {
            $this->activity([
                'exam_session_id' => $updatedSession->id,
                'type' => 'session_status_changed',
                'old_value' => $old,
                'new_value' => $target->code,
            ], $user);
        }

        return $updatedSession;
    }

    public function cancelSession(ExamSession $session, ?User $user = null, ?string $reason = null): ExamSession
    {
        return DB::transaction(function () use ($session, $user, $reason): ExamSession {
            $session = $this->changeSessionStatus($session, 'cancelled', $user, true);
            $session->participants()->update([
                'status' => 'cancelled',
                'admitted' => false,
                'block_reason' => $reason,
            ]);

            $this->syncSessionSeats($session);

            return $session->refresh();
        });
    }

    public function addStudentToSession(
        ExamSession $session,
        Student|int $student,
        StudentEnrollment|int $enrollment,
        ?User $user = null,
        bool $allowOverbooking = false,
        bool $admitted = true,
        ?string $blockReason = null,
    ): ExamParticipant {
        return DB::transaction(function () use ($session, $student, $enrollment, $user, $allowOverbooking, $admitted, $blockReason): ExamParticipant {
            $student = $student instanceof Student ? $student : Student::query()->findOrFail($student);
            $enrollment = $enrollment instanceof StudentEnrollment ? $enrollment : StudentEnrollment::query()->findOrFail($enrollment);

            if (! $this->studentCanJoinSession($session, $student, $enrollment, $allowOverbooking)) {
                throw ValidationException::withMessages([
                    'student_id' => tkey('exams.validation.student_cannot_join_session'),
                ]);
            }

            $participant = ExamParticipant::query()->updateOrCreate(
                [
                    'exam_session_id' => $session->id,
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                ],
                [
                    'status' => $admitted ? ExamParticipantStatus::Admitted->value : ExamParticipantStatus::Blocked->value,
                    'admitted' => $admitted,
                    'block_reason' => $blockReason,
                    'registered_at' => now(),
                ],
            );

            $this->syncSessionSeats($session);
            $this->activity([
                'exam_session_id' => $session->id,
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'type' => 'participant_added',
                'new_value' => $participant->status,
            ], $user);

            return $participant->refresh();
        });
    }

    public function removeStudentFromSession(ExamSession $session, Student|int $student, StudentEnrollment|int|null $enrollment = null, ?User $user = null): bool
    {
        return DB::transaction(function () use ($session, $student, $enrollment, $user): bool {
            $studentId = $student instanceof Student ? $student->id : (int) $student;
            $enrollmentId = $enrollment instanceof StudentEnrollment ? $enrollment->id : (filled($enrollment) ? (int) $enrollment : null);

            $participant = ExamParticipant::query()
                ->where('exam_session_id', $session->id)
                ->where('student_id', $studentId)
                ->when($enrollmentId !== null, fn ($query) => $query->where('enrollment_id', $enrollmentId))
                ->first();

            if ($participant === null) {
                return false;
            }

            $participant->delete();
            $this->syncSessionSeats($session);
            $this->activity([
                'exam_session_id' => $session->id,
                'student_id' => $studentId,
                'enrollment_id' => $enrollmentId,
                'type' => 'participant_removed',
                'old_value' => $participant->status,
            ], $user);

            return true;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function checkAdmission(StudentEnrollment $enrollment, ExamType|int|string $type, array $data = [], ?User $user = null): ExamAdmission
    {
        return DB::transaction(function () use ($enrollment, $type, $data, $user): ExamAdmission {
            $type = $this->examType($type);
            $admission = app(CreateOrUpdateExamAdmissionAction::class)->handle($enrollment, [
                'admission_type' => $this->legacyExamType($type)->value,
                ...$data,
            ], $user);

            $items = $this->buildAdmissionChecklist($admission, $type);
            $open = $items->contains(fn (ExamChecklistItem $item): bool => $item->required && $item->status !== ExamChecklistItemStatus::Passed->value);

            return $open
                ? $this->blockAdmission($admission, tkey('exams.validation.enrollment_cannot_take_exam'), $user)
                : $this->approveAdmission($admission, $user);
        });
    }

    public function buildAdmissionChecklist(ExamAdmission|StudentEnrollment $subject, ExamType|int|string|null $type = null, ?ExamSession $session = null, ?ExamAttempt $attempt = null): Collection
    {
        $admission = $subject instanceof ExamAdmission ? $subject : null;
        $enrollment = $subject instanceof StudentEnrollment ? $subject : $admission?->enrollment()->firstOrFail();
        $type = $type === null && $admission !== null
            ? $this->examType($admission->admission_type->value)
            : $this->examType($type ?? 'internal_theory');
        $rule = $this->admissionRule($enrollment, $type);
        $studentId = $enrollment->student_profile_id;

        $items = [
            'identity_document' => $this->documentAccepted($enrollment, ['id_card']),
            'medical_certificate' => $this->documentAccepted($enrollment, ['medical_certificate']),
            'training_contract' => $this->documentAccepted($enrollment, ['training_contract', 'contract']),
            'payment_clearance' => $this->paymentsCompleted($enrollment, $rule),
            'theory_hours' => $this->theoryHoursMet($enrollment, $rule?->required_theory_hours),
        ];

        if ((float) ($rule?->required_practice_hours ?? 0) > 0.0 || $type->is_practical) {
            $items['practice_hours'] = $this->practiceHoursMet($enrollment, $rule?->required_practice_hours);
        }

        if ($rule?->require_internal_exam_passed) {
            $items['internal_exam_passed'] = $this->internalExamPassed($enrollment, $type);
        }

        return collect($items)->map(function (bool $passed, string $key) use ($session, $attempt, $studentId, $enrollment): ExamChecklistItem {
            return ExamChecklistItem::query()->updateOrCreate(
                [
                    'exam_session_id' => $session?->id,
                    'attempt_id' => $attempt?->id,
                    'student_id' => $studentId,
                    'enrollment_id' => $enrollment->id,
                    'key' => $key,
                ],
                [
                    'title_translations' => null,
                    'status' => $passed ? ExamChecklistItemStatus::Passed->value : ExamChecklistItemStatus::Pending->value,
                    'required' => true,
                ],
            );
        })->values();
    }

    public function approveAdmission(ExamAdmission $admission, ?User $user = null): ExamAdmission
    {
        $admission->forceFill([
            'status' => ExamAdmissionStatus::Ready,
            'checklist_status' => 'passed',
            'admitted_at' => $admission->admitted_at ?? now(),
            'rejected_at' => null,
            'updated_by_id' => $user?->id ?? $admission->updated_by_id,
        ])->save();

        app(RecordExamActivityAction::class)->handle(
            $admission->refresh(),
            null,
            null,
            $user,
            'admission_approved',
            tkey('exams.activities.titles.admission_approved'),
        );

        return $admission->refresh();
    }

    public function blockAdmission(ExamAdmission $admission, ?string $reason = null, ?User $user = null): ExamAdmission
    {
        $admission->forceFill([
            'status' => ExamAdmissionStatus::Blocked,
            'checklist_status' => 'failed',
            'rejected_at' => now(),
            'internal_notes' => $reason ?? $admission->internal_notes,
            'updated_by_id' => $user?->id ?? $admission->updated_by_id,
        ])->save();

        app(RecordExamActivityAction::class)->handle(
            $admission->refresh(),
            null,
            null,
            $user,
            'admission_blocked',
            tkey('exams.activities.titles.admission_blocked'),
            $reason,
        );

        return $admission->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createAttempt(ExamSession $session, Student|int $student, StudentEnrollment|int $enrollment, array $data = [], ?User $user = null): ExamAttempt
    {
        return DB::transaction(function () use ($session, $student, $enrollment, $data, $user): ExamAttempt {
            $student = $student instanceof Student ? $student : Student::query()->findOrFail($student);
            $enrollment = $enrollment instanceof StudentEnrollment ? $enrollment : StudentEnrollment::query()->findOrFail($enrollment);
            $type = $session->typeRecord ?: $this->examType($session->exam_type->value);
            $legacyType = $this->legacyExamType($type);

            $participant = ExamParticipant::query()
                ->where('exam_session_id', $session->id)
                ->where('student_id', $student->id)
                ->where('enrollment_id', $enrollment->id)
                ->first();

            if ($participant === null) {
                $this->addStudentToSession($session, $student, $enrollment, $user, (bool) ($data['allow_overbooking'] ?? false));
                $session = $session->refresh();
            }

            $status = $this->attemptStatus($data['status_id'] ?? $data['status'] ?? 'allowed');
            $attemptNo = (int) ($data['attempt_no'] ?? $data['attempt_number'] ?? $this->generateAttemptNumber($enrollment, $type));
            $admission = ExamAdmission::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('admission_type', $legacyType->value)
                ->first();

            $attempt = ExamAttempt::query()->create([
                'exam_admission_id' => $data['exam_admission_id'] ?? $admission?->id,
                'exam_session_id' => $session->id,
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'student_profile_id' => $student->id,
                'training_group_id' => $session->training_group_id ?? $enrollment->training_group_id,
                'training_program_id' => $session->training_program_id ?? $enrollment->training_program_id,
                'instructor_id' => $data['instructor_id'] ?? $session->instructor_id ?? $enrollment->instructor_id,
                'exam_type' => $legacyType,
                'provider' => $type->is_official ? 'state' : 'internal',
                'status' => $this->legacyAttemptStatus($status->code),
                'status_id' => $status->id,
                'attempt_number' => $attemptNo,
                'attempt_no' => $attemptNo,
                'score' => $data['score'] ?? null,
                'max_score' => $data['max_score'] ?? null,
                'passed' => (bool) ($data['passed'] ?? false),
                'no_show' => false,
                'started_at' => $data['started_at'] ?? null,
                'finished_at' => $data['finished_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by_id' => $data['created_by_id'] ?? $user?->id,
                'updated_by_id' => $data['updated_by_id'] ?? $user?->id,
            ]);

            $this->buildAdmissionChecklist($enrollment, $type, $session, $attempt);
            $this->activity([
                'exam_session_id' => $session->id,
                'attempt_id' => $attempt->id,
                'exam_attempt_id' => $attempt->id,
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'type' => 'attempt_created',
                'new_value' => $status->code,
            ], $user);

            return $attempt->refresh();
        });
    }

    public function startAttempt(ExamAttempt $attempt, ?User $user = null): ExamAttempt
    {
        if (! $this->attemptCanStart($attempt)) {
            throw ValidationException::withMessages([
                'attempt_id' => tkey('exams.validation.attempt_cannot_start'),
            ]);
        }

        $status = $this->attemptStatus('in_progress');
        $attempt->forceFill([
            'status_id' => $status->id,
            'status' => LegacyExamAttemptStatus::InProgress,
            'started_at' => $attempt->started_at ?? now(),
            'updated_by_id' => $user?->id ?? $attempt->updated_by_id,
        ])->save();

        $this->activity([
            'exam_session_id' => $attempt->exam_session_id,
            'attempt_id' => $attempt->id,
            'exam_attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
            'enrollment_id' => $attempt->enrollment_id,
            'type' => 'attempt_started',
            'new_value' => 'in_progress',
        ], $user);

        return $attempt->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function completeAttempt(ExamAttempt $attempt, array $data, ?User $user = null): ExamAttempt
    {
        if (! $this->attemptCanComplete($attempt)) {
            throw ValidationException::withMessages([
                'attempt_id' => tkey('exams.validation.attempt_cannot_complete'),
            ]);
        }

        $passed = (bool) ($data['passed'] ?? false);
        $status = $this->attemptStatus($passed ? 'passed' : 'failed');
        $attempt->forceFill([
            'status_id' => $status->id,
            'status' => $this->legacyAttemptStatus($status->code),
            'score' => $data['score'] ?? $attempt->score,
            'max_score' => $data['max_score'] ?? $attempt->max_score,
            'passed' => $passed,
            'finished_at' => $data['finished_at'] ?? now(),
            'updated_by_id' => $user?->id ?? $attempt->updated_by_id,
        ])->save();

        $this->recordResult($attempt->refresh(), [
            'result_status' => $passed ? 'passed' : 'failed',
            ...$data,
        ], $user);

        return $attempt->refresh();
    }

    public function markAttemptNoShow(ExamAttempt $attempt, ?User $user = null): ExamAttempt
    {
        $status = $this->attemptStatus('no_show');
        $attempt->forceFill([
            'status_id' => $status->id,
            'status' => LegacyExamAttemptStatus::NoShow,
            'no_show' => true,
            'passed' => false,
            'finished_at' => $attempt->finished_at ?? now(),
            'updated_by_id' => $user?->id ?? $attempt->updated_by_id,
        ])->save();

        $this->activity([
            'exam_session_id' => $attempt->exam_session_id,
            'attempt_id' => $attempt->id,
            'exam_attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
            'enrollment_id' => $attempt->enrollment_id,
            'type' => 'attempt_no_show',
            'new_value' => 'no_show',
        ], $user);

        return $attempt->refresh();
    }

    public function cancelAttempt(ExamAttempt $attempt, ?User $user = null, ?string $reason = null): ExamAttempt
    {
        $status = $this->attemptStatus('cancelled');
        $attempt->forceFill([
            'status_id' => $status->id,
            'status' => LegacyExamAttemptStatus::Cancelled,
            'passed' => false,
            'finished_at' => $attempt->finished_at ?? now(),
            'internal_notes' => $reason ?? $attempt->internal_notes,
            'updated_by_id' => $user?->id ?? $attempt->updated_by_id,
        ])->save();

        $this->activity([
            'exam_session_id' => $attempt->exam_session_id,
            'attempt_id' => $attempt->id,
            'exam_attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
            'enrollment_id' => $attempt->enrollment_id,
            'type' => 'attempt_cancelled',
            'new_value' => 'cancelled',
        ], $user);

        return $attempt->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordResult(ExamAttempt $attempt, array $data, ?User $user = null): ExamResult
    {
        $fallbackStatus = (bool) ($data['passed'] ?? $attempt->passed) ? 'passed' : 'failed';
        $status = $this->resultStatus($data['result_status_id'] ?? $data['result_status'] ?? $fallbackStatus);
        $passed = (bool) ($data['passed'] ?? $status->code === 'passed');

        $result = ExamResult::query()->updateOrCreate(
            ['attempt_id' => $attempt->id],
            [
                'result_status_id' => $status->id,
                'score' => $data['score'] ?? $attempt->score,
                'max_score' => $data['max_score'] ?? $attempt->max_score,
                'passed' => $passed,
                'examiner_comment' => $data['examiner_comment'] ?? null,
                'mistakes_summary' => $data['mistakes_summary'] ?? null,
                'decided_by_id' => $data['decided_by_id'] ?? $user?->id,
                'decided_at' => $data['decided_at'] ?? now(),
            ],
        );

        $attemptStatus = $status->code === 'cancelled'
            ? $this->attemptStatus('cancelled')
            : $this->attemptStatus($passed ? 'passed' : 'failed');

        $attempt->forceFill([
            'status_id' => $attemptStatus->id,
            'status' => $this->legacyAttemptStatus($attemptStatus->code),
            'score' => $result->score,
            'max_score' => $result->max_score,
            'passed' => $passed,
            'finished_at' => $attempt->finished_at ?? now(),
            'updated_by_id' => $user?->id ?? $attempt->updated_by_id,
        ])->save();

        $this->activity([
            'exam_session_id' => $attempt->exam_session_id,
            'attempt_id' => $attempt->id,
            'exam_attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
            'enrollment_id' => $attempt->enrollment_id,
            'type' => 'result_recorded',
            'new_value' => $status->code,
        ], $user);

        return $result->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markPassed(ExamAttempt $attempt, array $data = [], ?User $user = null): ExamAttempt
    {
        $this->recordResult($attempt, [
            'result_status' => 'passed',
            'passed' => true,
            ...$data,
        ], $user);

        return $attempt->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markFailed(ExamAttempt $attempt, array $data = [], ?User $user = null): ExamAttempt
    {
        $this->recordResult($attempt, [
            'result_status' => $data['result_status'] ?? 'failed',
            'passed' => false,
            ...$data,
        ], $user);

        return $attempt->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRetakeRecord(ExamAttempt $previousAttempt, array $data = [], ?User $user = null): ExamRetake
    {
        if (! $this->retakeAllowed($previousAttempt)) {
            throw ValidationException::withMessages([
                'previous_attempt_id' => tkey('exams.validation.retake_not_allowed'),
            ]);
        }

        $studentId = $previousAttempt->student_id ?? $previousAttempt->student_profile_id;

        $retake = ExamRetake::query()->updateOrCreate(
            ['previous_attempt_id' => $previousAttempt->id],
            [
                'student_id' => $studentId,
                'enrollment_id' => $previousAttempt->enrollment_id,
                'new_attempt_id' => $data['new_attempt_id'] ?? null,
                'reason' => $data['reason'] ?? $data['notes'] ?? null,
                'planned_at' => $data['planned_at'] ?? $data['next_eligible_at'] ?? now()->addWeek(),
                'status' => $data['status'] ?? (filled($data['new_attempt_id'] ?? null) ? ExamRetakeStatus::Scheduled->value : ExamRetakeStatus::Planned->value),
            ],
        );

        $this->activity([
            'exam_session_id' => $previousAttempt->exam_session_id,
            'attempt_id' => $previousAttempt->id,
            'exam_attempt_id' => $previousAttempt->id,
            'student_id' => $studentId,
            'enrollment_id' => $previousAttempt->enrollment_id,
            'type' => 'retake_created',
            'new_value' => $retake->status,
        ], $user);

        return $retake->refresh();
    }

    public function scheduleRetake(ExamRetake $retake, ExamAttempt|int|null $newAttempt = null, ?User $user = null, mixed $plannedAt = null): ExamRetake
    {
        $newAttemptId = $newAttempt instanceof ExamAttempt ? $newAttempt->id : (filled($newAttempt) ? (int) $newAttempt : $retake->new_attempt_id);

        $retake->forceFill([
            'new_attempt_id' => $newAttemptId,
            'planned_at' => $plannedAt ?? $retake->planned_at ?? now()->addWeek(),
            'status' => $newAttemptId !== null ? ExamRetakeStatus::Scheduled->value : ExamRetakeStatus::Planned->value,
        ])->save();

        $this->activity([
            'attempt_id' => $newAttemptId ?? $retake->previous_attempt_id,
            'exam_attempt_id' => $newAttemptId ?? $retake->previous_attempt_id,
            'student_id' => $retake->student_id,
            'enrollment_id' => $retake->enrollment_id,
            'type' => 'retake_scheduled',
            'new_value' => $retake->status,
        ], $user);

        return $retake->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function activity(array $data, ?User $user = null): ExamActivity
    {
        return ExamActivity::query()->create([
            'exam_admission_id' => $data['exam_admission_id'] ?? null,
            'exam_session_id' => $data['exam_session_id'] ?? null,
            'exam_attempt_id' => $data['exam_attempt_id'] ?? $data['attempt_id'] ?? null,
            'attempt_id' => $data['attempt_id'] ?? $data['exam_attempt_id'] ?? null,
            'enrollment_id' => $data['enrollment_id'] ?? null,
            'student_profile_id' => $data['student_profile_id'] ?? $data['student_id'] ?? null,
            'student_id' => $data['student_id'] ?? $data['student_profile_id'] ?? null,
            'training_group_id' => $data['training_group_id'] ?? null,
            'user_id' => $data['user_id'] ?? $user?->id,
            'type' => $data['type'],
            'title' => $data['title'] ?? tkey('exams.activities.titles.'.($data['type'] ?? 'activity')),
            'body' => $data['body'] ?? null,
            'old_value' => $data['old_value'] ?? null,
            'new_value' => $data['new_value'] ?? null,
            'meta' => $data['meta'] ?? null,
        ]);
    }

    public function examType(ExamType|int|string|null $type): ExamType
    {
        if ($type instanceof ExamType) {
            return $type;
        }

        $query = ExamType::query();

        return is_numeric($type)
            ? $query->whereKey($type)->firstOrFail()
            : $query->where('code', (string) $type)->firstOrFail();
    }

    public function examStatus(ExamStatus|int|string|null $status): ExamStatus
    {
        if ($status instanceof ExamStatus) {
            return $status;
        }

        $status = $status === 'planned' ? 'scheduled' : $status;
        $query = ExamStatus::query();

        return is_numeric($status)
            ? $query->whereKey($status)->firstOrFail()
            : $query->where('code', (string) $status)->firstOrFail();
    }

    public function attemptStatus(ExamAttemptStatusModel|int|string|null $status): ExamAttemptStatusModel
    {
        if ($status instanceof ExamAttemptStatusModel) {
            return $status;
        }

        $status = $status === 'scheduled' ? 'planned' : $status;
        $query = ExamAttemptStatusModel::query();

        return is_numeric($status)
            ? $query->whereKey($status)->firstOrFail()
            : $query->where('code', (string) $status)->firstOrFail();
    }

    public function resultStatus(ExamResultStatus|int|string|null $status): ExamResultStatus
    {
        if ($status instanceof ExamResultStatus) {
            return $status;
        }

        $query = ExamResultStatus::query();

        return is_numeric($status)
            ? $query->whereKey($status)->firstOrFail()
            : $query->where('code', (string) $status)->firstOrFail();
    }

    public function activeExamType(mixed $value): bool
    {
        return filled($value) && ExamType::query()
            ->when(is_numeric($value), fn ($query) => $query->whereKey($value), fn ($query) => $query->where('code', (string) $value))
            ->where('is_active', true)
            ->exists();
    }

    public function activeExamStatus(mixed $value): bool
    {
        return filled($value) && ExamStatus::query()
            ->when(is_numeric($value), fn ($query) => $query->whereKey($value), fn ($query) => $query->where('code', (string) $value))
            ->where('is_active', true)
            ->exists();
    }

    public function canTransitionSessionStatus(ExamSession $session, ExamStatus|int|string $target, bool $allowOverride = false): bool
    {
        if ($allowOverride) {
            return true;
        }

        $from = $this->sessionStatusCode($session);
        $to = $target instanceof ExamStatus ? $target->code : $this->examStatus($target)->code;

        return $from === null || $from === $to || in_array($to, self::SESSION_TRANSITIONS[$from] ?? [], true);
    }

    public function sessionHasCapacity(ExamSession $session, ?int $capacity = null): bool
    {
        if ($capacity !== null) {
            return $capacity >= (int) $session->seats_taken;
        }

        return $this->takenSeats($session) < (int) $session->capacity;
    }

    public function studentCanJoinSession(ExamSession $session, Student $student, StudentEnrollment $enrollment, bool $allowOverbooking = false): bool
    {
        if ((int) $enrollment->student_profile_id !== (int) $student->id) {
            return false;
        }

        if (! $this->sessionAcceptsStudents($session)) {
            return false;
        }

        if (! $allowOverbooking && ! $this->sessionHasCapacity($session)) {
            return false;
        }

        if (
            filled($session->training_group_id)
            && filled($enrollment->training_group_id)
            && (int) $session->training_group_id !== (int) $enrollment->training_group_id
        ) {
            return false;
        }

        return ! ExamParticipant::query()
            ->where('exam_session_id', $session->id)
            ->where('student_id', $student->id)
            ->where('enrollment_id', $enrollment->id)
            ->exists();
    }

    public function enrollmentCanTakeExam(StudentEnrollment $enrollment, ExamType|int|string|null $type = null): bool
    {
        $type = $this->examType($type ?? 'internal_theory');
        $rule = $this->admissionRule($enrollment, $type);

        return ! in_array($enrollment->status?->value ?? (string) $enrollment->status, ['cancelled', 'archived'], true)
            && $this->documentsAccepted($enrollment, $rule)
            && $this->paymentsCompleted($enrollment, $rule)
            && $this->theoryHoursMet($enrollment, $rule?->required_theory_hours)
            && $this->practiceHoursMet($enrollment, $rule?->required_practice_hours)
            && $this->internalExamPassed($enrollment, $type);
    }

    public function documentsAccepted(StudentEnrollment $enrollment, ?ExamAdmissionRule $rule = null): bool
    {
        if ($rule !== null && ! $rule->require_documents) {
            return true;
        }

        return $this->documentAccepted($enrollment, ['id_card'])
            && $this->documentAccepted($enrollment, ['medical_certificate'])
            && $this->documentAccepted($enrollment, ['training_contract', 'contract']);
    }

    public function paymentsCompleted(StudentEnrollment $enrollment, ?ExamAdmissionRule $rule = null): bool
    {
        if ($rule !== null && ! $rule->require_no_debt) {
            return true;
        }

        if (in_array((string) $enrollment->payment_status, ['paid', 'completed', 'settled'], true)) {
            return true;
        }

        if ((int) $enrollment->contracted_price_cents > 0 && $enrollment->balanceCents() === 0) {
            return true;
        }

        $paidCents = (int) Payment::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', PaymentStatus::Paid->value)
            ->sum('amount_cents');

        return (int) $enrollment->contracted_price_cents > 0
            && $paidCents >= (int) $enrollment->contracted_price_cents;
    }

    public function theoryHoursMet(StudentEnrollment $enrollment, mixed $required = null): bool
    {
        $required = (float) ($required ?? $enrollment->total_theory_hours ?? 0);

        return $required <= 0.0 || (float) $enrollment->completed_theory_hours >= $required;
    }

    public function practiceHoursMet(StudentEnrollment $enrollment, mixed $required = null): bool
    {
        $required = (float) ($required ?? 0);

        return $required <= 0.0 || (float) $enrollment->completed_practice_hours >= $required;
    }

    public function internalExamPassed(StudentEnrollment $enrollment, ExamType|int|string|null $type = null): bool
    {
        $type = $this->examType($type ?? 'state_theory');

        if (! $type->is_official) {
            return true;
        }

        $requiredType = $type->is_practical ? LegacyExamType::InternalPractical : LegacyExamType::InternalTheory;

        return ExamAttempt::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('exam_type', $requiredType->value)
            ->where('passed', true)
            ->exists();
    }

    public function attemptCanStart(ExamAttempt $attempt): bool
    {
        $code = $this->attemptStatusCode($attempt);

        return in_array($code, ['planned', 'allowed', 'scheduled'], true)
            && $attempt->finished_at === null
            && ! (bool) $attempt->no_show;
    }

    public function attemptCanComplete(ExamAttempt $attempt): bool
    {
        return $this->attemptStatusCode($attempt) === 'in_progress'
            && $attempt->started_at !== null
            && $attempt->finished_at === null;
    }

    public function resultScoreValid(mixed $score, mixed $maxScore = null): bool
    {
        if (! is_numeric($score)) {
            return false;
        }

        if ((float) $score < 0.0) {
            return false;
        }

        return $maxScore === null || ! is_numeric($maxScore) || (float) $score <= (float) $maxScore;
    }

    public function retakeAllowed(ExamAttempt $attempt): bool
    {
        if ((bool) $attempt->passed) {
            return false;
        }

        $code = $this->attemptStatusCode($attempt);

        return in_array($code, ['failed', 'no_show', 'cancelled'], true);
    }

    public function admissionRule(StudentEnrollment $enrollment, ExamType $type): ?ExamAdmissionRule
    {
        $base = ExamAdmissionRule::query()->active()->forExamType($type);

        if (filled($enrollment->training_program_id) && filled($enrollment->course_category_id)) {
            $rule = (clone $base)
                ->where('course_id', $enrollment->training_program_id)
                ->where('course_category_id', $enrollment->course_category_id)
                ->first();

            if ($rule !== null) {
                return $rule;
            }
        }

        if (filled($enrollment->training_program_id)) {
            $rule = (clone $base)
                ->where('course_id', $enrollment->training_program_id)
                ->whereNull('course_category_id')
                ->first();

            if ($rule !== null) {
                return $rule;
            }
        }

        if (filled($enrollment->course_category_id)) {
            $rule = (clone $base)
                ->whereNull('course_id')
                ->where('course_category_id', $enrollment->course_category_id)
                ->first();

            if ($rule !== null) {
                return $rule;
            }
        }

        return (clone $base)
            ->whereNull('course_id')
            ->whereNull('course_category_id')
            ->first();
    }

    public function sessionStatusCode(ExamSession $session): ?string
    {
        if ($session->relationLoaded('statusRecord') && $session->statusRecord !== null) {
            return $session->statusRecord->code;
        }

        if ($session->status_id !== null) {
            return ExamStatus::query()->whereKey($session->status_id)->value('code');
        }

        return match ($session->status) {
            LegacyExamSessionStatus::Planned => 'scheduled',
            LegacyExamSessionStatus::Open => 'open',
            LegacyExamSessionStatus::Full => 'open',
            LegacyExamSessionStatus::InProgress => 'in_progress',
            LegacyExamSessionStatus::Completed => 'completed',
            LegacyExamSessionStatus::Cancelled => 'cancelled',
            default => null,
        };
    }

    public function attemptStatusCode(ExamAttempt $attempt): ?string
    {
        if ($attempt->relationLoaded('statusRecord') && $attempt->statusRecord !== null) {
            return $attempt->statusRecord->code;
        }

        if ($attempt->status_id !== null) {
            return ExamAttemptStatusModel::query()->whereKey($attempt->status_id)->value('code');
        }

        return match ($attempt->status) {
            LegacyExamAttemptStatus::Scheduled => 'scheduled',
            LegacyExamAttemptStatus::InProgress => 'in_progress',
            LegacyExamAttemptStatus::Passed => 'passed',
            LegacyExamAttemptStatus::Failed => 'failed',
            LegacyExamAttemptStatus::NoShow => 'no_show',
            LegacyExamAttemptStatus::Cancelled => 'cancelled',
            default => null,
        };
    }

    private function sessionAcceptsStudents(ExamSession $session): bool
    {
        return in_array($this->sessionStatusCode($session), ['scheduled', 'open'], true);
    }

    private function syncSessionSeats(ExamSession $session): void
    {
        $session->forceFill([
            'seats_taken' => min((int) $session->capacity, $this->takenSeats($session)),
        ])->save();
    }

    private function takenSeats(ExamSession $session): int
    {
        return ExamParticipant::query()
            ->where('exam_session_id', $session->id)
            ->whereIn('status', [
                ExamParticipantStatus::Registered->value,
                ExamParticipantStatus::Admitted->value,
                ExamParticipantStatus::Blocked->value,
            ])
            ->count();
    }

    private function ensureCapacityIsNotBelowTakenSeats(int $capacity, int $seatsTaken): void
    {
        if ($capacity < $seatsTaken) {
            throw ValidationException::withMessages([
                'capacity' => tkey('exams.validation.capacity_below_taken_seats'),
            ]);
        }
    }

    /**
     * @param  array<int, string>  $documentTypes
     */
    private function documentAccepted(StudentEnrollment $enrollment, array $documentTypes): bool
    {
        return StudentDocument::query()
            ->where('student_profile_id', $enrollment->student_profile_id)
            ->whereIn('document_type', $documentTypes)
            ->where(function ($query) use ($enrollment): void {
                $query->where('enrollment_id', $enrollment->id)
                    ->orWhereNull('enrollment_id');
            })
            ->where('status', DocumentStatus::Verified->value)
            ->exists();
    }

    private function legacyExamType(ExamType $type): LegacyExamType
    {
        return match ($type->code) {
            'internal_practical' => LegacyExamType::InternalPractical,
            'official_theory_placeholder', 'state_theory' => LegacyExamType::StateTheory,
            'official_practical_placeholder', 'state_practical' => LegacyExamType::StatePractical,
            default => LegacyExamType::InternalTheory,
        };
    }

    private function legacySessionStatus(string $code): LegacyExamSessionStatus
    {
        return match ($code) {
            'open' => LegacyExamSessionStatus::Open,
            'in_progress' => LegacyExamSessionStatus::InProgress,
            'completed' => LegacyExamSessionStatus::Completed,
            'cancelled', 'archived' => LegacyExamSessionStatus::Cancelled,
            default => LegacyExamSessionStatus::Planned,
        };
    }

    private function legacyAttemptStatus(string $code): LegacyExamAttemptStatus
    {
        return match ($code) {
            'in_progress' => LegacyExamAttemptStatus::InProgress,
            'passed' => LegacyExamAttemptStatus::Passed,
            'failed' => LegacyExamAttemptStatus::Failed,
            'no_show' => LegacyExamAttemptStatus::NoShow,
            'cancelled', 'archived' => LegacyExamAttemptStatus::Cancelled,
            default => LegacyExamAttemptStatus::Scheduled,
        };
    }
}
