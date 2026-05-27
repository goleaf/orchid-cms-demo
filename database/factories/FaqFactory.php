<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\Branch;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $question = $this->faker->sentence(8);
        $answer = $this->faker->paragraph();

        return [
            'uuid' => (string) Str::uuid(),
            'faqable_type' => null,
            'faqable_id' => null,
            'question_translations' => $this->translations($question),
            'answer_translations' => $this->translations($answer),
            'is_active' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function global(): static
    {
        return $this->state(fn (): array => [
            'faqable_type' => null,
            'faqable_id' => null,
        ]);
    }

    public function forCourse(Course|int|null $course = null): static
    {
        return $this->state(function () use ($course): array {
            $courseModel = $course instanceof Course ? $course : null;

            if ($courseModel === null && $course === null) {
                $courseModel = Course::factory()->create();
            }

            return [
                'faqable_type' => Course::class,
                'faqable_id' => $courseModel?->getKey() ?? $course,
            ];
        });
    }

    public function forBranch(Branch|int|null $branch = null): static
    {
        return $this->state(function () use ($branch): array {
            $branchModel = $branch instanceof Branch ? $branch : null;

            if ($branchModel === null && $branch === null) {
                $branchModel = Branch::factory()->create();
            }

            return [
                'faqable_type' => Branch::class,
                'faqable_id' => $branchModel?->getKey() ?? $branch,
            ];
        });
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'question_translations' => $this->translations('Можно ли выбрать группу?', 'Can I choose a group?', 'Ar galiu pasirinkti grupe?', 'Czy moge wybrac grupe?'),
            'answer_translations' => $this->translations('Да, менеджер поможет подобрать подходящую группу и филиал.', 'Yes, a manager will help you choose a suitable group and branch.', 'Taip, vadybininkas pades pasirinkti tinkama grupe ir filiala.', 'Tak, menedzer pomoze wybrac odpowiednia grupe i oddzial.'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, ?string $en = null, ?string $lt = null, ?string $pl = null): array
    {
        return [
            'ru' => $ru,
            'en' => $en ?? $ru,
            'lt' => $lt ?? $en ?? $ru,
            'pl' => $pl ?? $en ?? $ru,
        ];
    }
}
