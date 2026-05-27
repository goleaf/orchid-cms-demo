<?php

namespace App\Http\Requests;

use App\Models\LandingPage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandingPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('platform.content.home') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page.id' => ['required', 'integer', Rule::exists(LandingPage::class, 'id')],
            'page.title' => ['required', 'string', 'max:255'],
            'page.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('landing_pages', 'slug')->ignore($this->input('page.id')),
            ],
            'page.eyebrow' => ['nullable', 'string', 'max:255'],
            'page.hero_title' => ['required', 'string', 'max:255'],
            'page.hero_summary' => ['required', 'string', 'max:1000'],
            'page.primary_button_label' => ['nullable', 'string', 'max:80'],
            'page.primary_button_url' => ['nullable', 'string', 'max:255'],
            'page.secondary_button_label' => ['nullable', 'string', 'max:80'],
            'page.secondary_button_url' => ['nullable', 'string', 'max:255'],
            'page.about_heading' => ['required', 'string', 'max:255'],
            'page.about_body' => ['required', 'string', 'max:2000'],
            'page.offer_one_title' => ['nullable', 'string', 'max:255'],
            'page.offer_one_body' => ['nullable', 'string', 'max:1000'],
            'page.offer_two_title' => ['nullable', 'string', 'max:255'],
            'page.offer_two_body' => ['nullable', 'string', 'max:1000'],
            'page.offer_three_title' => ['nullable', 'string', 'max:255'],
            'page.offer_three_body' => ['nullable', 'string', 'max:1000'],
            'page.published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function pageData(): array
    {
        return $this->validated()['page'];
    }
}
