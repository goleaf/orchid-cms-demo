<?php

namespace App\Orchid\Screens\Website;

use App\Actions\MoveSortableOrderAction;
use App\Enums\SitePageType;
use App\Models\SitePage;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class SitePageListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    public function query(Request $request): iterable
    {
        $this->filters = $request->only(['search', 'type', 'active']);

        $pages = SitePage::query()
            ->select([
                'id',
                'type',
                'slug',
                'title_translations',
                'seo_title_translations',
                'seo_description_translations',
                'is_active',
                'is_indexable',
                'sort_order',
                'published_at',
                'updated_at',
            ])
            ->when(filled($this->filters['search'] ?? null), function (Builder $query): void {
                $search = (string) $this->filters['search'];
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('slug', 'like', '%'.$search.'%')
                        ->orWhere('type', 'like', '%'.$search.'%');
                });
            })
            ->when(filled($this->filters['type'] ?? null), fn (Builder $query) => $query->where('type', $this->filters['type']))
            ->when(($this->filters['active'] ?? '') !== '', fn (Builder $query) => $query->where('is_active', (bool) $this->filters['active']))
            ->ordered()
            ->simplePaginate(15)
            ->withQueryString();

        $this->applyOrderControlState($pages, SitePage::class);

        return [
            'pages' => $pages,
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.pages.title');
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
        return [
            Link::make(tkey('website.admin.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.website.pages.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('search')
                    ->title(tkey('website.admin.filters.search'))
                    ->value($this->filters['search'] ?? ''),
                Select::make('type')
                    ->title(tkey('website.admin.pages.fields.type'))
                    ->empty(tkey('website.admin.filters.all_types'), '')
                    ->options($this->pageTypeOptions())
                    ->value($this->filters['type'] ?? ''),
                Select::make('active')
                    ->title(tkey('website.admin.filters.status'))
                    ->empty(tkey('website.admin.filters.all_statuses'), '')
                    ->options([
                        '1' => tkey('website.admin.status.active'),
                        '0' => tkey('website.admin.status.inactive'),
                    ])
                    ->value($this->filters['active'] ?? ''),
                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ]),

            Layout::table('pages', [
                TD::make('title', tkey('website.admin.pages.columns.title'))
                    ->render(fn (SitePage $page): string => $page->displayTitle().' '.$this->seoWarning($page->displaySeoTitle(), $page->displaySeoDescription())),
                TD::make('slug', tkey('website.seo.fields.slug'))
                    ->render(fn (SitePage $page): string => $page->slug),
                TD::make('type', tkey('website.admin.pages.fields.type'))
                    ->render(fn (SitePage $page): string => tkey('website.admin.pages.types.'.($page->type ?: 'custom'))),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (SitePage $page): string => $this->booleanBadge($page->is_active, 'website.admin.status.active', 'website.admin.status.inactive')),
                TD::make('is_indexable', tkey('website.seo.fields.is_indexable'))
                    ->alignCenter()
                    ->render(fn (SitePage $page): string => $this->booleanBadge($page->is_indexable)),
                TD::make('published_at', tkey('website.admin.fields.published_at'))
                    ->render(fn (SitePage $page): string => $page->published_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('updated_at', tkey('website.admin.fields.updated_at'))
                    ->render(fn (SitePage $page): string => $page->updated_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('order_controls', tkey('website.admin.fields.position'))
                    ->alignCenter()
                    ->render(fn (SitePage $page): string => $this->orderControls($page)),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (SitePage $page): string => $this->pageActions($page)),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.website.pages', array_filter([
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'active' => $request->input('active'),
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    public function moveUp(Request $request, MoveSortableOrderAction $move): RedirectResponse
    {
        return $this->moveSortable($request, SitePage::class, 'platform.website.pages', $move, MoveSortableOrderAction::UP);
    }

    public function moveDown(Request $request, MoveSortableOrderAction $move): RedirectResponse
    {
        return $this->moveSortable($request, SitePage::class, 'platform.website.pages', $move, MoveSortableOrderAction::DOWN);
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

    private function pageActions(SitePage $page): string
    {
        $actions = [
            (string) Link::make(tkey('common.actions.edit'))
                ->icon('bs.pencil')
                ->route('platform.website.pages.edit', $page),
        ];

        $url = $this->publicPageUrl($page);

        if ($url !== null) {
            $actions[] = (string) Link::make(tkey('website.admin.actions.open_public_page'))
                ->icon('bs.box-arrow-up-right')
                ->href($url)
                ->target('_blank');
        }

        return implode(' ', $actions);
    }
}
