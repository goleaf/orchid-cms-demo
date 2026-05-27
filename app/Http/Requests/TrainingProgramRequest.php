<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Models\TrainingProgram;
use App\Services\TranslatableContentManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingProgramRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.lms.programs', 'website.manage_courses']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $programId = $this->input('program.id');

        return [
            'program.id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'program.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('training_programs', 'slug')->ignore($programId),
            ],
            'program.license_category' => ['required', 'string', 'max:40'],
            'program.transmission' => ['required', 'string', 'max:40'],
            'program.theory_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'program.practice_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'program.duration_weeks' => ['required', 'integer', 'min:1', 'max:200'],
            'program.format' => ['required', Rule::in(['offline', 'online', 'mixed'])],
            'program.price_eur' => ['required', 'numeric', 'min:0', 'max:100000'],
            'program.old_price_eur' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'program.image_path' => ['nullable', 'string', 'max:255'],
            'program.sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'program.is_active' => ['nullable', 'boolean'],
            'program.available_languages' => ['nullable', 'string', 'max:500'],
            'program.required_documents' => ['nullable', 'string', 'max:1000'],
            'program.admission_requirements' => ['nullable', 'string', 'max:2000'],
            'program.canonical_url' => ['nullable', 'url', 'max:255'],
            'program.open_graph_image' => ['nullable', 'string', 'max:255'],
            ...app(TranslatableContentManager::class)->validationRules([
                'title',
                'short_description',
                'description',
                'included_items',
                'extra_costs',
                'theory_program',
                'practice_program',
                'seo_title',
                'seo_description',
                'og_title',
                'og_description',
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function programData(): array
    {
        $validated = $this->validated();
        $program = $validated['program'];
        $translations = app(TranslatableContentManager::class)->extract($this, [
            'title',
            'short_description',
            'description',
            'included_items',
            'extra_costs',
            'theory_program',
            'practice_program',
            'seo_title',
            'seo_description',
            'og_title',
            'og_description',
        ]);

        return [
            'slug' => $program['slug'],
            'license_category' => $program['license_category'],
            'transmission' => $program['transmission'],
            'theory_hours' => (int) $program['theory_hours'],
            'practice_hours' => (int) $program['practice_hours'],
            'duration_weeks' => (int) $program['duration_weeks'],
            'format' => $program['format'],
            'price_cents' => $this->euroToCents($program['price_eur']),
            'old_price_cents' => filled($program['old_price_eur'] ?? null) ? $this->euroToCents($program['old_price_eur']) : null,
            'available_languages' => $this->lines($program['available_languages'] ?? null),
            'required_documents' => $this->lines($program['required_documents'] ?? null),
            'admission_requirements' => $program['admission_requirements'] ?? null,
            'canonical_url' => $program['canonical_url'] ?? null,
            'open_graph_image' => $program['open_graph_image'] ?? null,
            'image_path' => $program['image_path'] ?? null,
            'sort_order' => (int) ($program['sort_order'] ?? 0),
            'is_active' => (bool) ($program['is_active'] ?? false),
            ...$translations,
            'title' => $this->fallbackScalar($translations, 'title', tkey('website.courses.fields.name')),
            'short_description' => $this->fallbackScalar($translations, 'short_description'),
            'description' => $this->fallbackScalar($translations, 'description'),
            'included_items' => $this->fallbackScalar($translations, 'included_items'),
            'extra_costs' => $this->fallbackScalar($translations, 'extra_costs'),
            'theory_program' => $this->fallbackScalar($translations, 'theory_program'),
            'practice_program' => $this->fallbackScalar($translations, 'practice_program'),
            'seo_title' => $this->fallbackScalar($translations, 'seo_title'),
            'meta_description' => $this->fallbackScalar($translations, 'seo_description'),
            'og_title' => $this->fallbackScalar($translations, 'og_title'),
            'og_description' => $this->fallbackScalar($translations, 'og_description'),
        ];
    }

    private function euroToCents(mixed $value): int
    {
        return (int) round(((float) $value) * 100);
    }

    /**
     * @return array<int, string>|null
     */
    private function lines(?string $value): ?array
    {
        if (! filled($value)) {
            return null;
        }

        return str($value)
            ->replace(["\r\n", "\r"], "\n")
            ->explode("\n")
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function fallbackScalar(array $translations, string $field, ?string $fallback = null): ?string
    {
        $value = app(TranslatableContentManager::class)
            ->defaultValue($translations[$field.'_translations'] ?? []);

        return filled($value) ? (string) $value : $fallback;
    }
}
