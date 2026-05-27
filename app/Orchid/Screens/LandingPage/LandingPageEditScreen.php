<?php

declare(strict_types=1);

namespace App\Orchid\Screens\LandingPage;

use App\Actions\FindEditableHomePageAction;
use App\Actions\UpdateLandingPageAction;
use App\Http\Requests\LandingPageRequest;
use App\Models\LandingPage;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LandingPageEditScreen extends Screen
{
    public LandingPage $page;

    public function query(FindEditableHomePageAction $homePage): iterable
    {
        return [
            'page' => $homePage->handle(),
        ];
    }

    public function name(): ?string
    {
        return 'Homepage';
    }

    public function description(): ?string
    {
        return 'Edit the public mini website homepage.';
    }

    public function permission(): iterable
    {
        return [
            'platform.content.home',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('View site')
                ->icon('bs.box-arrow-up-right')
                ->route('site.home')
                ->target('_blank'),

            Button::make('Save')
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

                Input::make('page.title')
                    ->title('Site title')
                    ->maxlength(255)
                    ->required(),

                Input::make('page.slug')
                    ->title('Slug')
                    ->readonly()
                    ->required(),

                Input::make('page.eyebrow')
                    ->title('Eyebrow')
                    ->maxlength(255),

                Input::make('page.hero_title')
                    ->title('Hero title')
                    ->maxlength(255)
                    ->required(),

                TextArea::make('page.hero_summary')
                    ->title('Hero summary')
                    ->rows(4)
                    ->maxlength(1000)
                    ->required(),
            ])->title('Hero'),

            Layout::rows([
                Input::make('page.primary_button_label')
                    ->title('Primary button label')
                    ->maxlength(80),

                Input::make('page.primary_button_url')
                    ->title('Primary button URL')
                    ->maxlength(255),

                Input::make('page.secondary_button_label')
                    ->title('Secondary button label')
                    ->maxlength(80),

                Input::make('page.secondary_button_url')
                    ->title('Secondary button URL')
                    ->maxlength(255),
            ])->title('Calls to action'),

            Layout::rows([
                Input::make('page.about_heading')
                    ->title('Section heading')
                    ->maxlength(255)
                    ->required(),

                TextArea::make('page.about_body')
                    ->title('Section body')
                    ->rows(5)
                    ->maxlength(2000)
                    ->required(),
            ])->title('Content'),

            Layout::rows([
                Input::make('page.offer_one_title')
                    ->title('First card title')
                    ->maxlength(255),

                TextArea::make('page.offer_one_body')
                    ->title('First card body')
                    ->rows(3)
                    ->maxlength(1000),

                Input::make('page.offer_two_title')
                    ->title('Second card title')
                    ->maxlength(255),

                TextArea::make('page.offer_two_body')
                    ->title('Second card body')
                    ->rows(3)
                    ->maxlength(1000),

                Input::make('page.offer_three_title')
                    ->title('Third card title')
                    ->maxlength(255),

                TextArea::make('page.offer_three_body')
                    ->title('Third card body')
                    ->rows(3)
                    ->maxlength(1000),

                Input::make('page.published_at')
                    ->type('datetime-local')
                    ->title('Published at'),
            ])->title('Cards and publishing'),
        ];
    }

    public function save(LandingPageRequest $request, UpdateLandingPageAction $landingPage): RedirectResponse
    {
        $landingPage->handle($this->page, $request->pageData());

        Toast::info('Homepage saved.');

        return redirect()->route('platform.content.home');
    }
}
