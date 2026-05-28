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
            ['code' => 'planned', 'state' => 'planned'],
            ['code' => 'recruiting', 'state' => 'recruiting'],
            ['code' => 'open', 'state' => 'open'],
            ['code' => 'almost_full', 'state' => 'almostFull'],
            ['code' => 'active', 'state' => 'active'],
            ['code' => 'closed', 'state' => 'closed'],
            ['code' => 'completed', 'state' => 'completed'],
            ['code' => 'cancelled', 'state' => 'cancelled'],
        ]);

        TrainingGroupStatus::query()
            ->where('code', '!=', 'planned')
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
