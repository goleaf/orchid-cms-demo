<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\TrainingProgram;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ProgramListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'programs' => TrainingProgram::query()
                ->forAcademyList()
                ->withCount(['modules', 'enrollments'])
                ->orderBy('title')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'LMS Programs';
    }

    public function description(): ?string
    {
        return 'Training programs, modules, categories, and prices.';
    }

    public function permission(): iterable
    {
        return ['platform.lms.programs'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('programs', [
                TD::make('title', 'Program')
                    ->render(fn (TrainingProgram $program): string => $program->title),
                TD::make('license_category', 'Category')
                    ->render(fn (TrainingProgram $program): string => $program->license_category)
                    ->alignCenter(),
                TD::make('transmission', 'Transmission')
                    ->render(fn (TrainingProgram $program): string => str($program->transmission)->title()->toString()),
                TD::make('theory_hours', 'Theory')
                    ->render(fn (TrainingProgram $program): string => (string) $program->theory_hours)
                    ->alignCenter(),
                TD::make('practice_hours', 'Practice')
                    ->render(fn (TrainingProgram $program): string => (string) $program->practice_hours)
                    ->alignCenter(),
                TD::make('modules_count', 'Modules')
                    ->render(fn (TrainingProgram $program): string => (string) $program->modules_count)
                    ->alignCenter(),
                TD::make('enrollments_count', 'Enrollments')
                    ->render(fn (TrainingProgram $program): string => (string) $program->enrollments_count)
                    ->alignCenter(),
                TD::make('price_cents', 'Price')
                    ->render(fn (TrainingProgram $program): string => $program->priceForHumans()),
                TD::make('is_active', 'Active')
                    ->alignCenter()
                    ->render(fn (TrainingProgram $program): string => $program->is_active ? 'Yes' : 'No'),
            ]),
        ];
    }
}
