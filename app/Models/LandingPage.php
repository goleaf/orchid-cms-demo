<?php

namespace App\Models;

use Database\Factories\LandingPageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    /** @use HasFactory<LandingPageFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'eyebrow',
        'hero_title',
        'hero_summary',
        'primary_button_label',
        'primary_button_url',
        'secondary_button_label',
        'secondary_button_url',
        'about_heading',
        'about_body',
        'offer_one_title',
        'offer_one_body',
        'offer_two_title',
        'offer_two_body',
        'offer_three_title',
        'offer_three_body',
        'published_at',
    ];

    protected $casts = [
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
            'slug',
            'eyebrow',
            'hero_title',
            'hero_summary',
            'primary_button_label',
            'primary_button_url',
            'secondary_button_label',
            'secondary_button_url',
            'about_heading',
            'about_body',
            'offer_one_title',
            'offer_one_body',
            'offer_two_title',
            'offer_two_body',
            'offer_three_title',
            'offer_three_body',
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
}
