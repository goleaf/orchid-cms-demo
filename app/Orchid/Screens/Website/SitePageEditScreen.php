<?php

namespace App\Orchid\Screens\Website;

use App\Actions\CreateOrUpdateSitePageAction;
use App\Actions\PublishSitePageAction;
use App\Actions\UnpublishSitePageAction;
use App\Enums\SitePageType;
use App\Http\Requests\StoreSitePageRequest;
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

class SitePageEditScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public ?SitePage $page = null;

    public function query(?SitePage $page = null): iterable
    {
        $pageModel = $page?->exists
            ? $page
            : new SitePage([
                'type' => SitePageType::Custom->value,
                'slug' => '',
                'is_active' => true,
                'is_indexable' => true,
                'sort_order' => 0,
            ]);

        $this->page = $pageModel;

        return [
            'page' => $pageModel,
            'id' => $pageModel->id,
            'type' => $pageModel->type,
            'slug' => $pageModel->slug,
            'template' => $pageModel->template,
            'og_image' => $pageModel->og_image,
            'canonical_url' => $pageModel->canonical_url,
            'is_active' => $pageModel->is_active,
            'is_indexable' => $pageModel->is_indexable,
            'sort_order' => $pageModel->sort_order,
            'published_at' => $pageModel->published_at?->format('Y-m-d\TH:i'),
            'title_translations' => $this->translations($pageModel, 'title'),
            'subtitle_translations' => $this->translations($pageModel, 'subtitle'),
            'content_translations' => $this->translations($pageModel, 'content'),
            'excerpt_translations' => $this->translations($pageModel, 'excerpt'),
            'seo_title_translations' => $this->translations($pageModel, 'seo_title'),
            'seo_description_translations' => $this->translations($pageModel, 'seo_description'),
            'og_title_translations' => $this->translations($pageModel, 'og_title'),
            'og_description_translations' => $this->translations($pageModel, 'og_description'),
        ];
    }

    public function name(): ?string
    {
        return $this->page?->exists
            ? tkey('website.admin.pages.edit_title')
            : tkey('website.admin.pages.create_title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.pages.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_pages'];
    }

    public function commandBar(): iterable
    {
        $previewUrl = $this->page instanceof SitePage ? $this->publicPageUrl($this->page) : null;

        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.pages'),
            Link::make(tkey('website.admin.actions.preview'))
                ->icon('bs.box-arrow-up-right')
                ->href($previewUrl ?? route('site.home'))
                ->target('_blank')
                ->canSee($previewUrl !== null),
            Button::make(tkey('website.admin.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
            Button::make(tkey('website.admin.actions.publish'))
                ->icon('bs.upload')
                ->method('publish')
                ->canSee((bool) $this->page?->exists),
            Button::make(tkey('website.admin.actions.unpublish'))
                ->icon('bs.eye-slash')
                ->method('unpublish')
                ->canSee((bool) $this->page?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('id')->type('hidden'),
                Select::make('type')
                    ->title(tkey('website.admin.pages.fields.type'))
                    ->options($this->pageTypeOptions()),
                Input::make('slug')
                    ->title(tkey('website.seo.fields.slug'))
                    ->required(),
                Input::make('template')
                    ->title(tkey('website.admin.pages.fields.template')),
            ])->title(tkey('website.admin.sections.main')),

            TranslatableFields::input('title', 'website.admin.pages.fields.title', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'maxlength' => 255,
            ]),
            TranslatableFields::input('subtitle', 'website.admin.pages.fields.subtitle', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('excerpt', 'website.admin.pages.fields.excerpt', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 3,
            ]),
            TranslatableFields::textarea('content', 'website.admin.pages.fields.content', [
                'title_key' => 'website.admin.sections.content',
                'rows' => 8,
            ]),
            TranslatableFields::seo([
                'title_key' => 'website.admin.sections.seo',
                'keywords' => false,
            ]),

            Layout::rows([
                Input::make('og_image')
                    ->title(tkey('website.seo.fields.og_image')),
                Input::make('canonical_url')
                    ->title(tkey('website.seo.fields.canonical_url')),
                Select::make('is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options($this->booleanOptions()),
                Select::make('is_indexable')
                    ->title(tkey('website.seo.fields.is_indexable'))
                    ->options($this->booleanOptions()),
                Input::make('published_at')
                    ->type('datetime-local')
                    ->title(tkey('website.admin.fields.published_at')),
            ])->title(tkey('website.admin.sections.publishing')),
        ];
    }

    public function save(StoreSitePageRequest $request, CreateOrUpdateSitePageAction $save): RedirectResponse
    {
        $page = $this->resolveScreenModel($request, 'page', SitePage::class, 'slug')
            ?? $this->resolveScreenModel($request, 'sitePage', SitePage::class, 'slug');

        $save->handle($page, $this->validatedPayload($request, [
            'title',
            'subtitle',
            'content',
            'excerpt',
            'seo_title',
            'seo_description',
            'og_title',
            'og_description',
        ]));

        Toast::info(tkey('website.admin.pages.messages.saved'));

        return redirect()->route('platform.website.pages');
    }

    public function publish(PublishSitePageAction $publish): RedirectResponse
    {
        $page = $this->page?->exists ? $this->page : $this->resolveScreenModel(request(), 'page', SitePage::class, 'slug');

        if ($page instanceof SitePage && $page->exists) {
            $publish->handle($page);
            Toast::info(tkey('website.admin.pages.messages.published'));
        }

        return redirect()->route('platform.website.pages');
    }

    public function unpublish(UnpublishSitePageAction $unpublish): RedirectResponse
    {
        $page = $this->page?->exists ? $this->page : $this->resolveScreenModel(request(), 'page', SitePage::class, 'slug');

        if ($page instanceof SitePage && $page->exists) {
            $unpublish->handle($page);
            Toast::info(tkey('website.admin.pages.messages.unpublished'));
        }

        return redirect()->route('platform.website.pages');
    }

    /**
     * @return array<string, string>
     */
    private function pageTypeOptions(): array
    {
        return collect(SitePageType::values())
            ->mapWithKeys(fn (string $type): array => [$type => tkey('website.admin.pages.types.'.$type)])
            ->all();
    }
}
