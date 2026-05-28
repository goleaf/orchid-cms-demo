<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Faq;
use App\Models\Instructor;
use App\Models\PricingPackage;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\TrainingProgram;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetProgramCategoryPageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(TrainingProgram $program, ?Request $request = null): array
    {
        $request ??= request();
        $publicProgram = TrainingProgram::query()
            ->forAcademyList()
            ->addSelect(['course_category_id', 'duration_translations', 'price', 'old_price', 'currency', 'is_visible_on_site', 'is_indexable', 'is_featured'])
            ->active()
            ->visibleOnSite()
            ->with([
                'category:id,code,slug,name_translations',
                'groups' => fn ($query) => $query
                    ->select([
                        'id',
                        'branch_id',
                        'training_program_id',
                        'instructor_id',
                        'name',
                        'name_translations',
                        'schedule_summary_translations',
                        'code',
                        'group_number',
                        'status',
                        'capacity',
                        'places_taken',
                        'starts_on',
                        'meeting_days',
                        'meeting_time',
                    ])
                    ->with(['branch:id,name,name_translations,country,country_translations,city,city_translations', 'instructor:id,name'])
                    ->withCount('enrollments')
                    ->openForEnrollment()
                    ->orderBy('starts_on')
                    ->limit(8),
            ])
            ->whereKey($program->id)
            ->first();

        if ($publicProgram === null) {
            throw new NotFoundHttpException;
        }

        $branches = Branch::query()
            ->forAdminList()
            ->withCount('groups')
            ->active()
            ->visibleOnSite()
            ->ordered()
            ->get();
        $selectedBranch = $this->selectedBranch($request, $branches, $publicProgram);
        $applicationGroups = $selectedBranch === null
            ? $publicProgram->groups
            : $publicProgram->groups
                ->filter(fn ($group): bool => (int) $group->branch_id === (int) $selectedBranch->id)
                ->values();

        return [
            'program' => $publicProgram,
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'applicationGroups' => $applicationGroups,
            'pricingPackages' => PricingPackage::query()
                ->forPublicList()
                ->where('course_id', $publicProgram->id)
                ->active()
                ->visibleOnSite()
                ->ordered()
                ->get(),
            'faqs' => Faq::query()
                ->whereIn('faqable_type', [$publicProgram->getMorphClass(), Course::class, TrainingProgram::class])
                ->where('faqable_id', $publicProgram->id)
                ->active()
                ->ordered()
                ->get(),
            'testimonials' => Testimonial::query()
                ->forPublicList()
                ->with([
                    'branch:id,name,name_translations,country,country_translations,city,city_translations',
                ])
                ->where('training_program_id', $publicProgram->id)
                ->published()
                ->ordered()
                ->limit(6)
                ->get(),
            'settings' => SiteSetting::query()
                ->public()
                ->get(['key', 'value'])
                ->mapWithKeys(fn (SiteSetting $setting): array => [$setting->key => $setting->value])
                ->all(),
            'instructors' => Instructor::query()
                ->forPublicDirectory()
                ->with('branch:id,name,name_translations,country,country_translations,city,city_translations')
                ->where('status', 'active')
                ->whereJsonContains('categories', $publicProgram->license_category)
                ->orderByDesc('rating')
                ->limit(8)
                ->get(),
            'vehicles' => Vehicle::query()
                ->forFleetList()
                ->with(['branch:id,name,name_translations,country,country_translations,city,city_translations', 'instructor:id,name'])
                ->where('license_category', $publicProgram->license_category)
                ->orderBy('make')
                ->limit(8)
                ->get(),
            'seoTitle' => $publicProgram->displaySeoTitle().' | '.tkey('website.brand.name'),
            'seoDescription' => $publicProgram->displaySeoDescription(),
            'ogTitle' => $publicProgram->displayOgTitle().' | '.tkey('website.brand.name'),
            'ogDescription' => $publicProgram->displayOgDescription(),
            'canonical' => $publicProgram->canonical_url ?: route('website.courses.show', $publicProgram),
            'ogImage' => $publicProgram->open_graph_image ?: $publicProgram->og_image,
            'isIndexable' => $publicProgram->is_indexable,
        ];
    }

    /**
     * @param  Collection<int, Branch>  $branches
     */
    private function selectedBranch(Request $request, Collection $branches, TrainingProgram $program): ?Branch
    {
        $branchKey = trim((string) ($request->query('branch') ?: $request->query('branch_id', '')));

        if ($branchKey !== '') {
            $branch = $branches->first(fn (Branch $branch): bool => (string) $branch->id === $branchKey || $branch->slug === $branchKey);

            if ($branch !== null && $this->programHasBranch($program, $branch)) {
                return $branch;
            }
        }

        $country = trim((string) $request->query('country', ''));
        $city = trim((string) $request->query('city', ''));

        if ($country !== '' || $city !== '') {
            $branch = $branches->first(fn (Branch $branch): bool => ($country === '' || $branch->country === $country)
                && ($city === '' || $branch->city === $city)
                && $this->programHasBranch($program, $branch));

            if ($branch !== null) {
                return $branch;
            }
        }

        return $program->groups->first()?->branch;
    }

    private function programHasBranch(TrainingProgram $program, Branch $branch): bool
    {
        return $program->groups->contains(fn ($group): bool => (int) $group->branch_id === (int) $branch->id);
    }
}
