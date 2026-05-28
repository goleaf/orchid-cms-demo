<?php

namespace App\Orchid\Screens\Website;

use App\Actions\MoveFaqOrderAction;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Faq;
use App\Models\SitePage;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FaqListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public function query(): iterable
    {
        $faqs = Faq::query()
            ->select(['id', 'faqable_type', 'faqable_id', 'question_translations', 'is_active', 'sort_order'])
            ->with('faqable')
            ->ordered()
            ->simplePaginate(15);

        $firstId = Faq::query()->ordered()->value('id');
        $lastId = Faq::query()
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->value('id');

        $faqs->getCollection()->each(function (Faq $faq) use ($firstId, $lastId): void {
            $faq->setAttribute('can_move_up', $faq->getKey() !== $firstId);
            $faq->setAttribute('can_move_down', $faq->getKey() !== $lastId);
        });

        return [
            'faqs' => $faqs,
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.faq.title');
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
            Link::make(tkey('website.admin.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.website.faq.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('faqs', [
                TD::make('question', tkey('website.faq.fields.question'))
                    ->render(fn (Faq $faq): string => $faq->displayQuestion()),
                TD::make('related', tkey('website.admin.faq.fields.related_entity'))
                    ->render(fn (Faq $faq): string => $this->relatedLabel($faq)),
                TD::make('is_active', tkey('website.admin.fields.is_active'))
                    ->alignCenter()
                    ->render(fn (Faq $faq): string => $this->booleanBadge($faq->is_active, 'website.admin.status.active', 'website.admin.status.inactive')),
                TD::make('order_controls', tkey('website.admin.faq.fields.position'))
                    ->alignCenter()
                    ->render(fn (Faq $faq): string => $this->orderActions($faq)),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (Faq $faq): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.faq.edit', $faq)),
            ]),
        ];
    }

    public function moveUp(Request $request, MoveFaqOrderAction $move): RedirectResponse
    {
        $move->handle($this->resolveFaq($request), MoveFaqOrderAction::UP);

        Toast::info(tkey('website.admin.faq.messages.order_updated'));

        return redirect()->route('platform.website.faq');
    }

    public function moveDown(Request $request, MoveFaqOrderAction $move): RedirectResponse
    {
        $move->handle($this->resolveFaq($request), MoveFaqOrderAction::DOWN);

        Toast::info(tkey('website.admin.faq.messages.order_updated'));

        return redirect()->route('platform.website.faq');
    }

    private function relatedLabel(Faq $faq): string
    {
        if ($faq->faqable === null) {
            return tkey('website.admin.faq.related.global');
        }

        return match ($faq->faqable_type) {
            Course::class => tkey('website.admin.faq.related.course').': '.$faq->faqable->displayTitle(),
            Branch::class => tkey('website.admin.faq.related.branch').': '.$faq->faqable->displayName(),
            SitePage::class => tkey('website.admin.faq.related.page').': '.$faq->faqable->displayTitle(),
            default => class_basename((string) $faq->faqable_type).' #'.$faq->faqable_id,
        };
    }

    private function orderActions(Faq $faq): string
    {
        return implode(' ', [
            (string) Button::make(tkey('website.admin.actions.move_up'))
                ->icon('bs.arrow-up')
                ->method('moveUp')
                ->parameters(['id' => $faq->id])
                ->canSee((bool) $faq->getAttribute('can_move_up')),
            (string) Button::make(tkey('website.admin.actions.move_down'))
                ->icon('bs.arrow-down')
                ->method('moveDown')
                ->parameters(['id' => $faq->id])
                ->canSee((bool) $faq->getAttribute('can_move_down')),
        ]);
    }

    private function resolveFaq(Request $request): Faq
    {
        return Faq::query()->findOrFail($request->integer('id'));
    }
}
