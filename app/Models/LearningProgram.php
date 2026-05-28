<?php

namespace App\Models;

use Database\Factories\LearningProgramFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningProgram extends TrainingProgram
{
    protected $table = 'training_programs';

    protected static function newFactory(): Factory
    {
        return LearningProgramFactory::new();
    }

    public function learningModules(): HasMany
    {
        return $this->hasMany(LearningProgramModule::class, 'training_program_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(LearningTopic::class, 'training_program_id');
    }
}
