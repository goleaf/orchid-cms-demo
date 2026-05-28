<?php

namespace Database\Seeders;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class TrainingGroupStatusSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(TrainingGroupStatus::class, 'code', [
            ['code' => 'draft', 'state' => 'draft'],
            ['code' => 'recruiting', 'state' => 'recruiting'],
            ['code' => 'almost_full', 'state' => 'almostFull'],
            ['code' => 'full', 'state' => 'full'],
            ['code' => 'closed', 'state' => 'closed'],
            ['code' => 'scheduled', 'state' => 'scheduled'],
            ['code' => 'active', 'state' => 'active'],
            ['code' => 'paused', 'state' => 'paused'],
            ['code' => 'completed', 'state' => 'completed'],
            ['code' => 'cancelled', 'state' => 'cancelled'],
            ['code' => 'archived', 'state' => 'archived'],
            ['code' => 'planned', 'state' => 'planned'],
            ['code' => 'open', 'state' => 'open'],
            ['code' => 'in_progress', 'state' => 'inProgress'],
            ['code' => 'finished', 'state' => 'finished'],
        ]);

        TrainingGroupStatus::query()
            ->where('code', '!=', 'draft')
            ->update(['is_default' => false]);

        TrainingGroup::query()
            ->whereNull('status_id')
            ->get(['id', 'status'])
            ->each(function (TrainingGroup $group): void {
                $statusId = TrainingGroupStatus::query()
                    ->where('code', $group->status->value)
                    ->value('id');

                if ($statusId !== null) {
                    $group->forceFill(['status_id' => $statusId])->save();
                }
            });
    }
}
