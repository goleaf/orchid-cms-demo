<?php

namespace Database\Seeders;

use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus;
use App\Enums\ExamChecklistItemStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\ExamType;
use App\Models\ExamAdmission;
use App\Models\ExamAdmissionChecklistItem;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Models\StudentEnrollment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ExamDemoSeeder extends Seeder
{
    public function run(): void
    {
        $enrollment = StudentEnrollment::query()
            ->with(['student', 'trainingGroup'])
            ->first();

        if ($enrollment === null) {
            return;
        }

        $admission = ExamAdmission::query()->firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'admission_type' => ExamType::InternalPractical->value,
            ],
            Arr::except(
                ExamAdmission::factory()
                    ->forEnrollment($enrollment)
                    ->internalPractical()
                    ->ready()
                    ->make([
                        'status' => ExamAdmissionStatus::Ready,
                    ])
                    ->only((new ExamAdmission)->getFillable()),
                ['id'],
            ),
        );

        collect([
            ['code' => 'identity_document', 'document_type' => 'id_card'],
            ['code' => 'medical_certificate', 'document_type' => 'medical_certificate'],
            ['code' => 'training_contract', 'document_type' => 'training_contract'],
        ])->each(function (array $item) use ($admission, $enrollment): void {
            $document = StudentDocument::query()
                ->where('student_profile_id', $enrollment->student_profile_id)
                ->where('document_type', $item['document_type'])
                ->first();

            ExamAdmissionChecklistItem::query()->updateOrCreate(
                [
                    'exam_admission_id' => $admission->id,
                    'code' => $item['code'],
                ],
                ExamAdmissionChecklistItem::factory()
                    ->passed()
                    ->make([
                        'exam_admission_id' => $admission->id,
                        'code' => $item['code'],
                        'student_document_id' => $document?->id,
                        'source_type' => $document !== null ? StudentDocument::class : null,
                        'source_id' => $document?->id,
                    ])
                    ->only((new ExamAdmissionChecklistItem)->getFillable()),
            );
        });

        $payment = Payment::query()
            ->where('student_profile_id', $enrollment->student_profile_id)
            ->where('enrollment_id', $enrollment->id)
            ->first();

        ExamAdmissionChecklistItem::query()->updateOrCreate(
            [
                'exam_admission_id' => $admission->id,
                'code' => 'payment_clearance',
            ],
            ExamAdmissionChecklistItem::factory()
                ->passed()
                ->make([
                    'exam_admission_id' => $admission->id,
                    'code' => 'payment_clearance',
                    'payment_id' => $payment?->id,
                    'source_type' => $payment !== null ? Payment::class : null,
                    'source_id' => $payment?->id,
                ])
                ->only((new ExamAdmissionChecklistItem)->getFillable()),
        );

        foreach (['theory_hours', 'practice_hours'] as $code) {
            ExamAdmissionChecklistItem::query()->updateOrCreate(
                [
                    'exam_admission_id' => $admission->id,
                    'code' => $code,
                ],
                ExamAdmissionChecklistItem::factory()
                    ->passed()
                    ->make([
                        'exam_admission_id' => $admission->id,
                        'code' => $code,
                    ])
                    ->only((new ExamAdmissionChecklistItem)->getFillable()),
            );
        }

        $session = ExamSession::query()->firstOrCreate(
            ['external_reference' => 'DEMO-INTERNAL-PRACTICAL-001'],
            ExamSession::factory()
                ->forGroup($enrollment->trainingGroup)
                ->internalPractical()
                ->open()
                ->make([
                    'exam_type' => ExamType::InternalPractical,
                    'status' => ExamSessionStatus::Open,
                    'starts_at' => now()->addDays(14)->setTime(10, 0),
                    'ends_at' => now()->addDays(14)->setTime(11, 0),
                    'external_reference' => 'DEMO-INTERNAL-PRACTICAL-001',
                    'location' => 'Vilnius training area',
                    'capacity' => 4,
                    'seats_taken' => 1,
                ])
                ->only((new ExamSession)->getFillable()),
        );

        ExamAttempt::query()->firstOrCreate(
            [
                'exam_admission_id' => $admission->id,
                'attempt_number' => 1,
            ],
            ExamAttempt::factory()
                ->forAdmission($admission)
                ->forSession($session)
                ->make([
                    'status' => ExamAttemptStatus::Scheduled,
                    'passed' => false,
                ])
                ->only((new ExamAttempt)->getFillable()),
        );
    }
}
