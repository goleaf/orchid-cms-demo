<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Models\Branch;
use App\Services\TranslatableContentManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.operations.branches', 'website.manage_branches']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $branchId = $this->input('branch.id');

        return [
            'branch.id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'branch.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'slug')->ignore($branchId),
            ],
            'branch.phone' => ['nullable', 'string', 'max:80'],
            'branch.email' => ['nullable', 'email:rfc', 'max:190'],
            'branch.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'branch.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'branch.canonical_url' => ['nullable', 'url', 'max:255'],
            'branch.open_graph_image' => ['nullable', 'string', 'max:255'],
            'branch.sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'branch.is_active' => ['nullable', 'boolean'],
            ...app(TranslatableContentManager::class)->validationRules([
                'name',
                'city',
                'address',
                'description',
                'working_hours',
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
    public function branchData(): array
    {
        $validated = $this->validated();
        $branch = $validated['branch'];
        $translations = app(TranslatableContentManager::class)->extract($this, [
            'name',
            'city',
            'address',
            'description',
            'working_hours',
            'seo_title',
            'seo_description',
            'og_title',
            'og_description',
        ]);

        return [
            'slug' => $branch['slug'],
            'phone' => $branch['phone'] ?? null,
            'email' => $branch['email'] ?? null,
            'latitude' => $branch['latitude'] ?? null,
            'longitude' => $branch['longitude'] ?? null,
            'canonical_url' => $branch['canonical_url'] ?? null,
            'open_graph_image' => $branch['open_graph_image'] ?? null,
            'sort_order' => (int) ($branch['sort_order'] ?? 0),
            'is_active' => (bool) ($branch['is_active'] ?? false),
            ...$translations,
            'name' => $this->fallbackScalar($translations, 'name', tkey('website.branches.fields.name')),
            'city' => $this->fallbackScalar($translations, 'city', ''),
            'address' => $this->fallbackScalar($translations, 'address', ''),
            'description' => $this->fallbackScalar($translations, 'description'),
            'working_hours' => $this->fallbackScalar($translations, 'working_hours'),
            'seo_title' => $this->fallbackScalar($translations, 'seo_title'),
            'seo_description' => $this->fallbackScalar($translations, 'seo_description'),
            'og_title' => $this->fallbackScalar($translations, 'og_title'),
            'og_description' => $this->fallbackScalar($translations, 'og_description'),
        ];
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
