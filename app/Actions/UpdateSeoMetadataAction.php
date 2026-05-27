<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;

class UpdateSeoMetadataAction
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>  $titleFields
     * @param  array<int, string>  $descriptionFields
     */
    public function handle(
        Model $model,
        array $attributes,
        array $titleFields = ['title', 'name'],
        array $descriptionFields = ['excerpt', 'short_description', 'description', 'content'],
    ): Model {
        $metadata = app(GenerateSeoMetadataAction::class)->handle($attributes, $titleFields, $descriptionFields);
        $seoFields = collect([
            'seo_title',
            'seo_title_translations',
            'seo_description',
            'seo_description_translations',
            'meta_description',
            'og_title',
            'og_title_translations',
            'og_description',
            'og_description_translations',
            'og_image',
            'open_graph_image',
            'canonical_url',
            'is_indexable',
        ]);

        $model->fill($seoFields
            ->filter(fn (string $field): bool => array_key_exists($field, $metadata))
            ->mapWithKeys(fn (string $field): array => [$field => $metadata[$field]])
            ->all());
        $model->save();

        return $model->refresh();
    }
}
