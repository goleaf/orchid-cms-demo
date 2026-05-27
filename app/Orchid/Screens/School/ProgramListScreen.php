<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\TrainingProgram;
use Orchid\Screen\Actions\Link;
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
                ->orderBy('sort_order')
                ->orderBy('title')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.courses.title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.courses.description');
    }

    public function permission(): iterable
    {
        return ['platform.lms.programs', 'website.manage_courses'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('website.admin.courses.create_title'))
                ->icon('bs.plus-circle')
                ->route('platform.website.courses.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('programs', [
                TD::make('title', tkey('website.admin.courses.fields.title'))
                    ->render(fn (TrainingProgram $program): string => $program->displayTitle()),
                TD::make('license_category', tkey('website.admin.courses.fields.license_category'))
                    ->render(fn (TrainingProgram $program): string => $program->license_category)
                    ->alignCenter(),
                TD::make('transmission', tkey('website.admin.courses.fields.transmission'))
                    ->render(fn (TrainingProgram $program): string => tkey('website.transmissions.'.$program->transmission)),
                TD::make('theory_hours', tkey('website.admin.courses.fields.theory_hours'))
                    ->render(fn (TrainingProgram $program): string => (string) $program->theory_hours)
                    ->alignCenter(),
                TD::make('practice_hours', tkey('website.admin.courses.fields.practice_hours'))
                    ->render(fn (TrainingProgram $program): string => (string) $program->practice_hours)
                    ->alignCenter(),
                TD::make('modules_count', tkey('website.admin.courses.columns.modules'))
                    ->render(fn (TrainingProgram $program): string => (string) $program->modules_count)
                    ->alignCenter(),
                TD::make('enrollments_count', tkey('website.admin.courses.columns.enrollments'))
                    ->render(fn (TrainingProgram $program): string => (string) $program->enrollments_count)
                    ->alignCenter(),
                TD::make('price_cents', tkey('website.admin.courses.fields.price'))
                    ->render(fn (TrainingProgram $program): string => $program->priceForHumans()),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (TrainingProgram $program): string => $program->is_active ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (TrainingProgram $program): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.courses.edit', $program)),
            ]),
        ];
    }
}
