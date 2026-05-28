<?php

namespace Database\Seeders;

use App\Models\EnrollmentStatus;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class EnrollmentStatusSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(EnrollmentStatus::class, 'code', [
            ['code' => 'draft', 'state' => 'draft'],
            ['code' => 'waiting_documents', 'state' => 'waitingDocuments'],
            ['code' => 'waiting_payment', 'state' => 'waitingPayment'],
            ['code' => 'waiting_start', 'state' => 'waitingStart'],
            ['code' => 'active', 'state' => 'active'],
            ['code' => 'theory', 'state' => 'theory'],
            ['code' => 'practice', 'state' => 'practice'],
            ['code' => 'ready_internal_exam', 'state' => 'readyInternalExam'],
            ['code' => 'ready_state_exam', 'state' => 'readyStateExam'],
            ['code' => 'completed', 'state' => 'completed'],
            ['code' => 'paused', 'state' => 'paused'],
            ['code' => 'cancelled', 'state' => 'cancelled'],
            ['code' => 'expelled', 'state' => 'expelled'],
            ['code' => 'archived', 'state' => 'archived'],
        ]);

        EnrollmentStatus::query()
            ->where('code', '!=', 'waiting_documents')
            ->update(['is_default' => false]);
    }
}
