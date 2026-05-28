<?php

namespace Database\Factories;

use App\Enums\ExamChecklistItemStatus;
use App\Models\DrivingLesson;
use App\Models\ExamAdmission;
use App\Models\ExamAdmissionChecklistItem;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAdmissionChecklistItem>
 */
class ExamAdmissionChecklistItemFactory extends Factory
{
    public function definition(): array
    {
        $code = $this->faker->randomElement([
            'identity_document',
            'medical_certificate',
            'training_contract',
            'payment_clearance',
            'theory_hours',
            'practice_hours',
        ]);

        $titleTranslations = [
            'identity_document' => ['ru' => 'Документ личности', 'en' => 'Identity Document', 'lt' => 'Asmens dokumentas', 'pl' => 'Dokument tozsamosci'],
            'medical_certificate' => ['ru' => 'Медицинская справка', 'en' => 'Medical Certificate', 'lt' => 'Medicinos pazyma', 'pl' => 'Zaswiadczenie lekarskie'],
            'training_contract' => ['ru' => 'Договор обучения', 'en' => 'Training Contract', 'lt' => 'Mokymo sutartis', 'pl' => 'Umowa szkoleniowa'],
            'payment_clearance' => ['ru' => 'Проверка оплаты', 'en' => 'Payment Clearance', 'lt' => 'Mokejimo patikra', 'pl' => 'Rozliczenie platnosci'],
            'theory_hours' => ['ru' => 'Часы теории', 'en' => 'Theory Hours', 'lt' => 'Teorijos valandos', 'pl' => 'Godziny teorii'],
            'practice_hours' => ['ru' => 'Часы практики', 'en' => 'Practice Hours', 'lt' => 'Praktikos valandos', 'pl' => 'Godziny praktyki'],
        ];

        return [
            'exam_admission_id' => ExamAdmission::factory(),
            'code' => $code,
            'title_translations' => $titleTranslations[$code],
            'status' => ExamChecklistItemStatus::Pending,
            'source_type' => null,
            'source_id' => null,
            'student_document_id' => null,
            'payment_id' => null,
            'driving_lesson_id' => null,
            'checked_at' => null,
            'checked_by_id' => null,
            'notes' => null,
            'meta' => null,
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamChecklistItemStatus::Passed,
            'checked_at' => now(),
            'checked_by_id' => User::factory(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => ExamChecklistItemStatus::Failed,
            'checked_at' => now(),
            'checked_by_id' => User::factory(),
        ]);
    }

    public function forAdmission(ExamAdmission $admission): static
    {
        return $this->state(fn (): array => [
            'exam_admission_id' => $admission->id,
        ]);
    }

    public function forDocument(StudentDocument $document, string $code = 'identity_document'): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'student_document_id' => $document->id,
            'source_type' => StudentDocument::class,
            'source_id' => $document->id,
        ]);
    }

    public function forPayment(Payment $payment): static
    {
        return $this->state(fn (): array => [
            'code' => 'payment_clearance',
            'payment_id' => $payment->id,
            'source_type' => Payment::class,
            'source_id' => $payment->id,
        ]);
    }

    public function forDrivingLesson(DrivingLesson $lesson): static
    {
        return $this->state(fn (): array => [
            'code' => 'practice_hours',
            'driving_lesson_id' => $lesson->id,
            'source_type' => DrivingLesson::class,
            'source_id' => $lesson->id,
        ]);
    }
}
