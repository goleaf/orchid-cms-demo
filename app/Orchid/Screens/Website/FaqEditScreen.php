<?php

namespace App\Orchid\Screens\Website;

use App\Actions\CreateOrUpdateFaqAction;
use App\Http\Requests\StoreFaqRequest;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Faq;
use App\Models\SitePage;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FaqEditScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public ?Faq $faq = null;

    public function query(?Faq $faq = null): iterable
    {
        $faqModel = $faq?->exists
            ? $faq
            : new Faq([
                'is_active' => true,
                'sort_order' => 0,
            ]);

        $this->faq = $faqModel;

        return [
            'faq' => $faqModel,
            'id' => $faqModel->id,
            'faqable_type' => $faqModel->faqable_type,
            'faqable_id' => $faqModel->faqable_id,
            'is_active' => $faqModel->is_active,
            'sort_order' => $faqModel->sort_order,
            'question_translations' => $this->translations($faqModel, 'question'),
            'answer_translations' => $this->translations($faqModel, 'answer'),
        ];
    }

    public function name(): ?string
    {
        return $this->faq?->exists
            ? tkey('website.admin.faq.edit_title')
            : tkey('website.admin.faq.create_title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.faq.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_faq'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.faq'),
            Button::make(tkey('website.admin.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('id')->type('hidden'),
                Select::make('faqable_type')
                    ->title(tkey('website.admin.faq.fields.faqable_type'))
                    ->empty(tkey('website.admin.faq.related.global'), '')
                    ->options([
                        Course::class => tkey('website.admin.faq.related.course'),
                        Branch::class => tkey('website.admin.faq.related.branch'),
                        SitePage::class => tkey('website.admin.faq.related.page'),
                    ]),
                Input::make('faqable_id')
                    ->type('number')
                    ->title(tkey('website.admin.faq.fields.faqable_id')),
                Select::make('is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options($this->booleanOptions()),
                Input::make('sort_order')
                    ->type('number')
                    ->title(tkey('website.admin.fields.sort_order')),
            ])->title(tkey('website.admin.sections.main')),

            TranslatableFields::input('question', 'website.faq.fields.question', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'maxlength' => 500,
            ]),
            TranslatableFields::textarea('answer', 'website.faq.fields.answer', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'rows' => 6,
            ]),
        ];
    }

    public function save(StoreFaqRequest $request, CreateOrUpdateFaqAction $save): RedirectResponse
    {
        $faq = $this->resolveScreenModel($request, 'faq', Faq::class);

        $save->handle($faq, $this->validatedPayload($request, [
            'question',
            'answer',
        ]));

        Toast::info(tkey('website.admin.faq.messages.saved'));

        return redirect()->route('platform.website.faq');
    }
}
