<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\LandingPageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    /** @use HasFactory<LandingPageFactory> */
    use HasFactory;

    use HasTranslations;

    protected $fillable = [
        'title',
        'title_translations',
        'slug',
        'eyebrow',
        'eyebrow_translations',
        'hero_title',
        'hero_title_translations',
        'hero_summary',
        'hero_summary_translations',
        'primary_button_label',
        'primary_button_url',
        'secondary_button_label',
        'secondary_button_url',
        'about_heading',
        'about_heading_translations',
        'about_body',
        'about_body_translations',
        'offer_one_title',
        'offer_one_title_translations',
        'offer_one_body',
        'offer_one_body_translations',
        'offer_two_title',
        'offer_two_title_translations',
        'offer_two_body',
        'offer_two_body_translations',
        'offer_three_title',
        'offer_three_title_translations',
        'offer_three_body',
        'offer_three_body_translations',
        'published_at',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'eyebrow_translations' => 'array',
        'hero_title_translations' => 'array',
        'hero_summary_translations' => 'array',
        'about_heading_translations' => 'array',
        'about_body_translations' => 'array',
        'offer_one_title_translations' => 'array',
        'offer_one_body_translations' => 'array',
        'offer_two_title_translations' => 'array',
        'offer_two_body_translations' => 'array',
        'offer_three_title_translations' => 'array',
        'offer_three_body_translations' => 'array',
        'published_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function scopePublicHome(Builder $query): Builder
    {
        return $query
            ->select($this->publicColumns())
            ->where('slug', 'home')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeEditableHome(Builder $query): Builder
    {
        return $query
            ->select($this->editableColumns())
            ->where('slug', 'home');
    }

    /**
     * @return array<int, array{title: string, body: string}>
     */
    public function offerCards(): array
    {
        return collect([
            ['title' => $this->offer_one_title, 'body' => $this->offer_one_body],
            ['title' => $this->offer_two_title, 'body' => $this->offer_two_body],
            ['title' => $this->offer_three_title, 'body' => $this->offer_three_body],
        ])
            ->filter(fn (array $offer): bool => filled($offer['title']) || filled($offer['body']))
            ->map(fn (array $offer): array => [
                'title' => (string) $offer['title'],
                'body' => (string) $offer['body'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function publicColumns(): array
    {
        return [
            'id',
            'title',
            'title_translations',
            'slug',
            'eyebrow',
            'eyebrow_translations',
            'hero_title',
            'hero_title_translations',
            'hero_summary',
            'hero_summary_translations',
            'primary_button_label',
            'primary_button_url',
            'secondary_button_label',
            'secondary_button_url',
            'about_heading',
            'about_heading_translations',
            'about_body',
            'about_body_translations',
            'offer_one_title',
            'offer_one_title_translations',
            'offer_one_body',
            'offer_one_body_translations',
            'offer_two_title',
            'offer_two_title_translations',
            'offer_two_body',
            'offer_two_body_translations',
            'offer_three_title',
            'offer_three_title_translations',
            'offer_three_body',
            'offer_three_body_translations',
            'published_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function editableColumns(): array
    {
        return [
            ...$this->publicColumns(),
            'created_at',
            'updated_at',
        ];
    }

    public function displayTitle(?string $locale = null): string
    {
        return $this->getTranslation('title', $locale)
            ?: $this->title
            ?: tkey('website.brand.name');
    }

    public function displayText(string $field, ?string $locale = null): ?string
    {
        return $this->getTranslation($field, $locale)
            ?: $this->getAttribute($field);
    }

    /**
     * @return array<int, array{title: string, body: string}>
     */
    public function translatedOfferCards(?string $locale = null): array
    {
        return collect([
            ['title' => $this->displayText('offer_one_title', $locale), 'body' => $this->displayText('offer_one_body', $locale)],
            ['title' => $this->displayText('offer_two_title', $locale), 'body' => $this->displayText('offer_two_body', $locale)],
            ['title' => $this->displayText('offer_three_title', $locale), 'body' => $this->displayText('offer_three_body', $locale)],
        ])
            ->filter(fn (array $offer): bool => filled($offer['title']) || filled($offer['body']))
            ->map(fn (array $offer): array => [
                'title' => (string) $offer['title'],
                'body' => (string) $offer['body'],
            ])
            ->values()
            ->all();
    }
}
