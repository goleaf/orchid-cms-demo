<?php

namespace App\Orchid\Screens\Website;

use App\Actions\CreateOrUpdateTestimonialAction;
use App\Http\Requests\StoreTestimonialRequest;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\StudentProfile;
use App\Models\Testimonial;
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

class TestimonialEditScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public ?Testimonial $testimonial = null;

    /**
     * @var array<int, string>
     */
    private array $courses = [];

    /**
     * @var array<int, string>
     */
    private array $branches = [];

    /**
     * @var array<int, string>
     */
    private array $instructors = [];

    /**
     * @var array<int, string>
     */
    private array $students = [];

    public function query(?Testimonial $testimonial = null): iterable
    {
        $testimonialModel = $testimonial?->exists
            ? $testimonial
            : new Testimonial([
                'is_active' => true,
                'is_featured' => false,
                'published_at' => now(),
                'sort_order' => 0,
            ]);

        $this->testimonial = $testimonialModel;
        $this->courses = Course::query()
            ->select(['id', 'title', 'title_translations', 'name_translations', 'slug', 'sort_order'])
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Course $course): array => [$course->id => $course->displayTitle()])
            ->all();
        $this->branches = Branch::query()
            ->forAdminList()
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();
        $this->instructors = Instructor::query()
            ->forAdminList()
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();
        $this->students = StudentProfile::query()
            ->forCrmList()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (StudentProfile $student): array => [$student->id => $student->fullName()])
            ->all();

        return [
            'testimonial' => $testimonialModel,
            'id' => $testimonialModel->id,
            'course_id' => $testimonialModel->training_program_id,
            'branch_id' => $testimonialModel->branch_id,
            'instructor_id' => $testimonialModel->instructor_id,
            'student_id' => $testimonialModel->student_profile_id,
            'rating' => $testimonialModel->rating,
            'image' => $testimonialModel->image,
            'video_url' => $testimonialModel->video_url,
            'is_active' => $testimonialModel->is_active,
            'is_featured' => $testimonialModel->is_featured,
            'published_at' => $testimonialModel->published_at?->format('Y-m-d\TH:i'),
            'sort_order' => $testimonialModel->sort_order,
            'name_translations' => $this->translations($testimonialModel, 'name', $testimonialModel->author_name),
            'text_translations' => $this->translations($testimonialModel, 'text', $testimonialModel->body),
        ];
    }

    public function name(): ?string
    {
        return $this->testimonial?->exists
            ? tkey('website.admin.testimonials.edit_title')
            : tkey('website.admin.testimonials.create_title');
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
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.testimonials'),
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
                Select::make('course_id')
                    ->title(tkey('website.groups.fields.course'))
                    ->empty(tkey('website.admin.filters.no_course'), '')
                    ->options($this->courses),
                Select::make('branch_id')
                    ->title(tkey('website.groups.fields.branch'))
                    ->empty(tkey('website.admin.filters.no_branch'), '')
                    ->options($this->branches),
                Select::make('instructor_id')
                    ->title(tkey('crm.leads.fields.instructor'))
                    ->empty(tkey('crm.leads.empty.no_instructor'), '')
                    ->options($this->instructors),
                Select::make('student_id')
                    ->title(tkey('website.admin.testimonials.fields.student'))
                    ->empty(tkey('website.admin.filters.no_student'), '')
                    ->options($this->students),
                Input::make('rating')
                    ->type('number')
                    ->min(1)
                    ->max(5)
                    ->title(tkey('website.testimonials.fields.rating')),
                Input::make('image')
                    ->title(tkey('website.admin.fields.image_path')),
                Input::make('video_url')
                    ->title(tkey('website.admin.testimonials.fields.video_url')),
                Select::make('is_active')
                    ->title(tkey('website.admin.fields.is_active'))
                    ->options($this->booleanOptions()),
                Select::make('is_featured')
                    ->title(tkey('website.admin.fields.is_featured'))
                    ->options($this->booleanOptions()),
                Input::make('published_at')
                    ->type('datetime-local')
                    ->title(tkey('website.admin.fields.published_at')),
                Input::make('sort_order')
                    ->type('number')
                    ->title(tkey('website.admin.fields.sort_order')),
            ])->title(tkey('website.admin.sections.main')),

            TranslatableFields::input('name', 'website.testimonials.fields.name', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'maxlength' => 255,
            ]),
            TranslatableFields::textarea('text', 'website.testimonials.fields.text', [
                'title_key' => 'website.admin.sections.content',
                'required' => true,
                'rows' => 6,
            ]),
        ];
    }

    public function save(StoreTestimonialRequest $request, CreateOrUpdateTestimonialAction $save): RedirectResponse
    {
        $testimonial = $this->resolveScreenModel($request, 'testimonial', Testimonial::class);

        $save->handle($testimonial, $this->validatedPayload($request, [
            'name',
            'text',
        ]));

        Toast::info(tkey('website.admin.testimonials.messages.saved'));

        return redirect()->route('platform.website.testimonials');
    }
}
