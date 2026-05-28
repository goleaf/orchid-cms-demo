<?php

namespace App\Orchid\Screens\Website;

use App\Actions\MoveSortableOrderAction;
use App\Models\Testimonial;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class TestimonialListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public function query(): iterable
    {
        $testimonials = Testimonial::query()
            ->select([
                'id',
                'training_program_id',
                'branch_id',
                'author_name',
                'name_translations',
                'rating',
                'is_active',
                'is_featured',
                'published_at',
                'sort_order',
            ])
            ->with([
                'course:id,title,title_translations,name_translations,slug',
                'branch:id,name,name_translations,slug',
            ])
            ->ordered()
            ->simplePaginate(50);

        $this->applyOrderControlState($testimonials, Testimonial::class);

        return [
            'testimonials' => $testimonials,
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.testimonials.title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.testimonials.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_testimonials'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('website.admin.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.website.testimonials.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('testimonials', [
                TD::make('name', tkey('website.testimonials.fields.name'))
                    ->render(fn (Testimonial $testimonial): string => $testimonial->displayName()),
                TD::make('rating', tkey('website.testimonials.fields.rating'))
                    ->alignCenter()
                    ->render(fn (Testimonial $testimonial): string => $testimonial->rating !== null ? (string) $testimonial->rating : '-'),
                TD::make('course', tkey('website.groups.fields.course'))
                    ->render(fn (Testimonial $testimonial): string => $testimonial->course?->displayTitle() ?? '-'),
                TD::make('branch', tkey('website.groups.fields.branch'))
                    ->render(fn (Testimonial $testimonial): string => $testimonial->branch?->displayName() ?? '-'),
                TD::make('is_featured', tkey('website.admin.fields.is_featured'))
                    ->alignCenter()
                    ->render(fn (Testimonial $testimonial): string => $this->booleanBadge($testimonial->is_featured)),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (Testimonial $testimonial): string => $this->booleanBadge($testimonial->is_active, 'website.admin.status.active', 'website.admin.status.inactive')),
                TD::make('published_at', tkey('website.admin.fields.published_at'))
                    ->render(fn (Testimonial $testimonial): string => $testimonial->published_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('order_controls', tkey('website.admin.fields.position'))
                    ->alignCenter()
                    ->render(fn (Testimonial $testimonial): string => $this->orderControls($testimonial)),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (Testimonial $testimonial): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.testimonials.edit', $testimonial)),
            ]),
        ];
    }

    public function moveUp(Request $request, MoveSortableOrderAction $move): RedirectResponse
    {
        return $this->moveSortable($request, Testimonial::class, 'platform.website.testimonials', $move, MoveSortableOrderAction::UP);
    }

    public function moveDown(Request $request, MoveSortableOrderAction $move): RedirectResponse
    {
        return $this->moveSortable($request, Testimonial::class, 'platform.website.testimonials', $move, MoveSortableOrderAction::DOWN);
    }
}
