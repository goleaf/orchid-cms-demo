<?php

namespace App\Support\Crm;

use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadTag;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LeadDictionaryRegistry
{
    /**
     * @return array<string, array{
     *     model: class-string<Model>,
     *     key_column: string,
     *     title_key: string,
     *     description_key: string,
     *     menu_key: string
     * }>
     */
    public static function definitions(): array
    {
        return [
            'statuses' => [
                'model' => LeadStatus::class,
                'key_column' => 'code',
                'title_key' => 'crm.dictionaries.statuses.title',
                'description_key' => 'crm.dictionaries.statuses.description',
                'menu_key' => 'menu.crm.statuses',
            ],
            'sources' => [
                'model' => LeadSource::class,
                'key_column' => 'code',
                'title_key' => 'crm.dictionaries.sources.title',
                'description_key' => 'crm.dictionaries.sources.description',
                'menu_key' => 'menu.crm.sources',
            ],
            'lost-reasons' => [
                'model' => LeadLostReason::class,
                'key_column' => 'code',
                'title_key' => 'crm.dictionaries.lost_reasons.title',
                'description_key' => 'crm.dictionaries.lost_reasons.description',
                'menu_key' => 'menu.crm.lost_reasons',
            ],
            'tags' => [
                'model' => LeadTag::class,
                'key_column' => 'slug',
                'title_key' => 'crm.dictionaries.tags.title',
                'description_key' => 'crm.dictionaries.tags.description',
                'menu_key' => 'menu.crm.tags',
            ],
        ];
    }

    /**
     * @return array{
     *     model: class-string<Model>,
     *     key_column: string,
     *     title_key: string,
     *     description_key: string,
     *     menu_key: string
     * }
     */
    public static function definition(string $dictionary): array
    {
        return static::definitions()[$dictionary]
            ?? throw new NotFoundHttpException('Unknown CRM dictionary.');
    }
}
