<?php

namespace App\Actions;

use App\Enums\ExamSessionStatus;
use App\Enums\ExamType;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleExamSessionAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): ExamSession
    {
        return DB::transaction(function () use ($data, $user): ExamSession {
            $capacity = (int) ($data['capacity'] ?? 1);
            $seatsTaken = (int) ($data['seats_taken'] ?? 0);

            if ($capacity < $seatsTaken) {
                throw ValidationException::withMessages([
                    'capacity' => tkey('exams.validation.capacity_below_taken_seats'),
                ]);
            }

            $type = $this->examType($data['exam_type'] ?? ExamType::InternalTheory->value);
            $session = ExamSession::query()->updateOrCreate(
                filled($data['id'] ?? null) ? ['id' => (int) $data['id']] : ['uuid' => $data['uuid'] ?? null],
                [
                    'branch_id' => $data['branch_id'] ?? null,
                    'training_program_id' => $data['training_program_id'] ?? null,
                    'training_group_id' => $data['training_group_id'] ?? null,
                    'instructor_id' => $data['instructor_id'] ?? null,
                    'vehicle_id' => $data['vehicle_id'] ?? null,
                    'exam_type' => $type,
                    'provider' => $data['provider'] ?? $type->provider(),
                    'status' => $data['status'] ?? ExamSessionStatus::Planned,
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'] ?? null,
                    'location' => $data['location'] ?? null,
                    'capacity' => $capacity,
                    'seats_taken' => $seatsTaken,
                    'external_reference' => $data['external_reference'] ?? null,
                    'official_placeholder_payload' => $data['official_placeholder_payload'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'internal_notes' => $data['internal_notes'] ?? null,
                    'created_by_id' => $data['created_by_id'] ?? $user?->id,
                    'updated_by_id' => $user?->id,
                ],
            );

            app(RecordExamActivityAction::class)->handle(
                null,
                $session->refresh(),
                null,
                $user,
                'session_scheduled',
                tkey('exams.activities.titles.session_scheduled'),
                null,
                null,
                $session->status->value,
                ['exam_type' => $type->value],
            );

            return $session->refresh();
        });
    }

    private function examType(ExamType|string $type): ExamType
    {
        return $type instanceof ExamType ? $type : ExamType::from((string) $type);
    }
}
