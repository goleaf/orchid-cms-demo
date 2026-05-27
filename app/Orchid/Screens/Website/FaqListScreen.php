<?php

namespace App\Orchid\Screens\Website;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Faq;
use App\Models\SitePage;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class FaqListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public function query(): iterable
    {
        return [
            'faqs' => Faq::query()
                ->select(['id', 'faqable_type', 'faqable_id', 'question_translations', 'is_active', 'sort_order'])
                ->with('faqable')
                ->ordered()
                ->simplePaginate(15),
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
                TD::make('sort_order', tkey('website.admin.fields.sort_order'))
                    ->alignCenter()
                    ->render(fn (Faq $faq): string => (string) $faq->sort_order),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (Faq $faq): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.faq.edit', $faq)),
            ]),
        ];
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
}
