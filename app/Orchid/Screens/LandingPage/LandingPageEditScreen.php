<?php

declare(strict_types=1);

namespace App\Orchid\Screens\LandingPage;

use App\Actions\FindEditableHomePageAction;
use App\Actions\UpdateLandingPageAction;
use App\Http\Requests\LandingPageRequest;
use App\Models\LandingPage;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LandingPageEditScreen extends Screen
{
    public ?LandingPage $page = null;

    public function query(FindEditableHomePageAction $homePage): iterable
    {
        $page = $homePage->handle();

        return [
            'page' => $page,
            'title_translations' => $page->getTranslations('title') ?: ['ru' => $page->title],
            'eyebrow_translations' => $page->getTranslations('eyebrow') ?: ['ru' => $page->eyebrow],
            'hero_title_translations' => $page->getTranslations('hero_title') ?: ['ru' => $page->hero_title],
            'hero_summary_translations' => $page->getTranslations('hero_summary') ?: ['ru' => $page->hero_summary],
            'about_heading_translations' => $page->getTranslations('about_heading') ?: ['ru' => $page->about_heading],
            'about_body_translations' => $page->getTranslations('about_body') ?: ['ru' => $page->about_body],
            'offer_one_title_translations' => $page->getTranslations('offer_one_title') ?: ['ru' => $page->offer_one_title],
            'offer_one_body_translations' => $page->getTranslations('offer_one_body') ?: ['ru' => $page->offer_one_body],
            'offer_two_title_translations' => $page->getTranslations('offer_two_title') ?: ['ru' => $page->offer_two_title],
            'offer_two_body_translations' => $page->getTranslations('offer_two_body') ?: ['ru' => $page->offer_two_body],
            'offer_three_title_translations' => $page->getTranslations('offer_three_title') ?: ['ru' => $page->offer_three_title],
            'offer_three_body_translations' => $page->getTranslations('offer_three_body') ?: ['ru' => $page->offer_three_body],
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.home.title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.home.description');
    }

    public function permission(): iterable
    {
        return [
            'platform.content.home',
            'website.manage_settings',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('website.admin.actions.view_site'))
                ->icon('bs.box-arrow-up-right')
                ->route('site.home')
                ->target('_blank'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('page.id')
                    ->type('hidden'),

                Input::make('page.slug')
                    ->title(tkey('website.admin.fields.slug'))
                    ->readonly()
                    ->required(),

                Input::make('page.published_at')
                    ->type('datetime-local')
                    ->title(tkey('website.admin.fields.published_at')),
            ])->title(tkey('website.admin.sections.system')),

            TranslatableFields::input('title', 'website.admin.home.fields.title', [
                'title_key' => 'website.admin.sections.hero',
                'maxlength' => 255,
                'required' => true,
            ]),
            TranslatableFields::input('eyebrow', 'website.admin.home.fields.eyebrow', [
                'title_key' => 'website.admin.sections.hero',
                'maxlength' => 255,
            ]),
            TranslatableFields::input('hero_title', 'website.admin.home.fields.hero_title', [
                'title_key' => 'website.admin.sections.hero',
                'maxlength' => 255,
                'required' => true,
            ]),
            TranslatableFields::textarea('hero_summary', 'website.admin.home.fields.hero_summary', [
                'title_key' => 'website.admin.sections.hero',
                'rows' => 4,
                'maxlength' => 1000,
                'required' => true,
            ]),
            TranslatableFields::input('about_heading', 'website.admin.home.fields.about_heading', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
                'required' => true,
            ]),
            TranslatableFields::textarea('about_body', 'website.admin.home.fields.about_body', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 5,
                'maxlength' => 2000,
                'required' => true,
            ]),
            TranslatableFields::input('offer_one_title', 'website.admin.home.fields.offer_one_title', [
                'title_key' => 'website.admin.sections.benefits',
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('offer_one_body', 'website.admin.home.fields.offer_one_body', [
                'title_key' => 'website.admin.sections.benefits',
                'rows' => 3,
                'maxlength' => 1000,
            ]),
            TranslatableFields::input('offer_two_title', 'website.admin.home.fields.offer_two_title', [
                'title_key' => 'website.admin.sections.benefits',
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('offer_two_body', 'website.admin.home.fields.offer_two_body', [
                'title_key' => 'website.admin.sections.benefits',
                'rows' => 3,
                'maxlength' => 1000,
            ]),
            TranslatableFields::input('offer_three_title', 'website.admin.home.fields.offer_three_title', [
                'title_key' => 'website.admin.sections.benefits',
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('offer_three_body', 'website.admin.home.fields.offer_three_body', [
                'title_key' => 'website.admin.sections.benefits',
                'rows' => 3,
                'maxlength' => 1000,
            ]),
        ];
    }

    public function save(LandingPageRequest $request, UpdateLandingPageAction $landingPage): RedirectResponse
    {
        $page = $this->page ?? LandingPage::query()->editableHome()->firstOrFail();

        $landingPage->handle($page, $request->pageData());

        Toast::info(tkey('website.admin.home.messages.saved'));

        return redirect()->route('platform.content.home');
    }
}
