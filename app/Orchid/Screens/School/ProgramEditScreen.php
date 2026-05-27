<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\SaveTrainingProgramAction;
use App\Http\Requests\TrainingProgramRequest;
use App\Models\TrainingProgram;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ProgramEditScreen extends Screen
{
    /**
     * @var TrainingProgram|null
     */
    public $program = null;

    public function query(?TrainingProgram $program = null): iterable
    {
        $programModel = $program?->exists
            ? $program
            : new TrainingProgram([
                'slug' => '',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 40,
                'practice_hours' => 30,
                'duration_weeks' => 8,
                'format' => 'mixed',
                'price_cents' => 0,
                'sort_order' => 0,
                'is_active' => true,
            ]);
        $this->program = $programModel;

        return [
            'program' => $programModel,
            'program.price_eur' => $programModel->price_cents !== null ? number_format($programModel->price_cents / 100, 2, '.', '') : null,
            'program.old_price_eur' => $programModel->old_price_cents !== null ? number_format($programModel->old_price_cents / 100, 2, '.', '') : null,
            'program.available_languages' => implode("\n", $programModel->available_languages ?? []),
            'program.required_documents' => implode("\n", $programModel->required_documents ?? []),
            'title_translations' => $this->translations($programModel, 'title', $programModel->title),
            'short_description_translations' => $this->translations($programModel, 'short_description', $programModel->short_description),
            'description_translations' => $this->translations($programModel, 'description', $programModel->description),
            'included_items_translations' => $this->translations($programModel, 'included_items', $programModel->included_items),
            'extra_costs_translations' => $this->translations($programModel, 'extra_costs', $programModel->extra_costs),
            'theory_program_translations' => $this->translations($programModel, 'theory_program', $programModel->theory_program),
            'practice_program_translations' => $this->translations($programModel, 'practice_program', $programModel->practice_program),
            'seo_title_translations' => $this->translations($programModel, 'seo_title', $programModel->seo_title),
            'seo_description_translations' => $this->translations($programModel, 'seo_description', $programModel->meta_description),
            'og_title_translations' => $this->translations($programModel, 'og_title', $programModel->og_title),
            'og_description_translations' => $this->translations($programModel, 'og_description', $programModel->og_description),
        ];
    }

    public function name(): ?string
    {
        return $this->program?->exists
            ? tkey('website.admin.courses.edit_title')
            : tkey('website.admin.courses.create_title');
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
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.courses'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('program.id')->type('hidden'),
                Input::make('program.slug')
                    ->title(tkey('website.admin.fields.slug'))
                    ->required(),
                Input::make('program.license_category')
                    ->title(tkey('website.admin.courses.fields.license_category'))
                    ->required(),
                Select::make('program.transmission')
                    ->title(tkey('website.admin.courses.fields.transmission'))
                    ->options([
                        'manual' => tkey('website.transmissions.manual'),
                        'automatic' => tkey('website.transmissions.automatic'),
                    ])
                    ->required(),
                Select::make('program.format')
                    ->title(tkey('crm.leads.fields.preferred_format'))
                    ->options([
                        'offline' => tkey('website.formats.offline'),
                        'online' => tkey('website.formats.online'),
                        'mixed' => tkey('website.formats.mixed'),
                    ])
                    ->required(),
                Input::make('program.duration_weeks')
                    ->type('number')
                    ->title(tkey('website.admin.courses.fields.duration_weeks'))
                    ->required(),
                Input::make('program.theory_hours')
                    ->type('number')
                    ->title(tkey('website.admin.courses.fields.theory_hours'))
                    ->required(),
                Input::make('program.practice_hours')
                    ->type('number')
                    ->title(tkey('website.admin.courses.fields.practice_hours'))
                    ->required(),
                Input::make('program.price_eur')
                    ->type('number')
                    ->step('0.01')
                    ->title(tkey('website.admin.courses.fields.price'))
                    ->required(),
                Input::make('program.old_price_eur')
                    ->type('number')
                    ->step('0.01')
                    ->title(tkey('website.admin.courses.fields.old_price')),
                TextArea::make('program.available_languages')
                    ->title(tkey('website.admin.courses.fields.available_languages'))
                    ->rows(3),
                TextArea::make('program.required_documents')
                    ->title(tkey('website.admin.courses.fields.required_documents'))
                    ->rows(3),
                TextArea::make('program.admission_requirements')
                    ->title(tkey('website.admin.courses.fields.admission_requirements'))
                    ->rows(3),
                Input::make('program.image_path')
                    ->title(tkey('website.admin.fields.image_path')),
                Input::make('program.canonical_url')
                    ->title(tkey('website.admin.fields.canonical_url')),
                Input::make('program.open_graph_image')
                    ->title(tkey('website.admin.fields.open_graph_image')),
                Input::make('program.sort_order')
                    ->type('number')
                    ->title(tkey('website.admin.fields.sort_order')),
                Select::make('program.is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
            ])->title(tkey('website.admin.sections.system')),

            TranslatableFields::input('title', 'website.admin.courses.fields.title', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
                'required' => true,
            ]),
            TranslatableFields::textarea('short_description', 'website.admin.courses.fields.short_description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 3,
                'maxlength' => 1000,
            ]),
            TranslatableFields::textarea('description', 'website.admin.courses.fields.description', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 5,
            ]),
            TranslatableFields::textarea('included_items', 'website.admin.courses.fields.included_items', [
                'title_key' => 'website.admin.sections.price',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('extra_costs', 'website.admin.courses.fields.extra_costs', [
                'title_key' => 'website.admin.sections.price',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('theory_program', 'website.admin.courses.fields.theory_program', [
                'title_key' => 'website.admin.sections.curriculum',
                'rows' => 4,
            ]),
            TranslatableFields::textarea('practice_program', 'website.admin.courses.fields.practice_program', [
                'title_key' => 'website.admin.sections.curriculum',
                'rows' => 4,
            ]),
            TranslatableFields::seo([
                'title_key' => 'website.admin.sections.seo',
                'keywords' => false,
            ]),
        ];
    }

    public function save(TrainingProgramRequest $request, SaveTrainingProgramAction $save): RedirectResponse
    {
        $program = filled($request->input('program.id'))
            ? TrainingProgram::query()->findOrFail($request->integer('program.id'))
            : new TrainingProgram;

        $save->handle($program, $request->programData());

        Toast::info(tkey('website.admin.courses.messages.saved'));

        return redirect()->route('platform.website.courses');
    }

    /**
     * @return array<string, mixed>
     */
    private function translations(TrainingProgram $program, string $field, mixed $fallback): array
    {
        return $program->getTranslations($field) ?: ['ru' => $fallback];
    }
}
