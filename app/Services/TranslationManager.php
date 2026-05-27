<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class TranslationManager
{
    /**
     * @return array{languages: array<int, string>, translations: array<int, array<string, mixed>>}
     */
    public function exportArray(): array
    {
        $languageCodes = Language::activeCodes();

        $translations = TranslationString::query()
            ->select(['id', 'group', 'key', 'description', 'is_system'])
            ->with(['values:id,translation_string_id,language_code,value,is_approved'])
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(fn (TranslationString $translationString): array => [
                'key' => $translationString->key,
                'group' => $translationString->group,
                'description' => $translationString->description,
                'is_system' => $translationString->is_system,
                'values' => collect($languageCodes)
                    ->mapWithKeys(fn (string $code): array => [
                        $code => $translationString->values->firstWhere('language_code', $code)?->value,
                    ])
                    ->all(),
            ])
            ->all();

        return [
            'languages' => $languageCodes,
            'translations' => $translations,
        ];
    }

    public function exportCsv(): string
    {
        $export = $this->exportArray();
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'key',
            'group',
            'description',
            ...$export['languages'],
        ]);

        foreach ($export['translations'] as $translation) {
            fputcsv($handle, [
                $translation['key'],
                $translation['group'],
                $translation['description'],
                ...collect($export['languages'])
                    ->map(fn (string $code): ?string => $translation['values'][$code] ?? null)
                    ->all(),
            ]);
        }

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    /**
     * @param  array<string, array{value?: string|null, is_approved?: bool}>  $values
     */
    public function saveTranslationString(TranslationString $translationString, array $data, array $values): TranslationString
    {
        $translationString->fill($data);
        $translationString->save();

        foreach (Language::activeCodes() as $languageCode) {
            $value = $values[$languageCode] ?? [];

            TranslationValue::query()->updateOrCreate(
                [
                    'translation_string_id' => $translationString->id,
                    'language_code' => $languageCode,
                ],
                [
                    'value' => $value['value'] ?? null,
                    'is_approved' => (bool) ($value['is_approved'] ?? true),
                ],
            );
        }

        return $translationString;
    }

    public function createMissingValues(): int
    {
        $created = 0;
        $languageCodes = Language::activeCodes();

        TranslationString::query()
            ->select(['id'])
            ->chunkById(100, function (Collection $translationStrings) use ($languageCodes, &$created): void {
                foreach ($translationStrings as $translationString) {
                    foreach ($languageCodes as $languageCode) {
                        $value = TranslationValue::query()->firstOrCreate(
                            [
                                'translation_string_id' => $translationString->id,
                                'language_code' => $languageCode,
                            ],
                            [
                                'value' => null,
                                'is_approved' => true,
                            ],
                        );

                        if ($value->wasRecentlyCreated) {
                            $created++;
                        }
                    }
                }
            });

        return $created;
    }

    public function importUploadedFile(UploadedFile $file): int
    {
        $contents = $file->get();

        if ($contents === false) {
            throw new FileException('The uploaded translation file could not be read.');
        }

        return $this->importContents($contents, $file->getClientOriginalExtension());
    }

    public function importContents(string $contents, ?string $extension = null): int
    {
        $extension = strtolower((string) $extension);

        $rows = $extension === 'json' || str_starts_with(ltrim($contents), '{') || str_starts_with(ltrim($contents), '[')
            ? $this->jsonRows($contents)
            : $this->csvRows($contents);

        $updated = 0;

        foreach ($rows as $row) {
            $key = trim((string) ($row['key'] ?? ''));

            if ($key === '') {
                continue;
            }

            $translationString = TranslationString::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => $row['group'] ?? null,
                    'description' => $row['description'] ?? null,
                    'is_system' => (bool) ($row['is_system'] ?? false),
                ],
            );

            $values = $row['values'] ?? collect(Language::activeCodes())
                ->mapWithKeys(fn (string $code): array => [$code => $row[$code] ?? null])
                ->all();

            foreach ($values as $languageCode => $value) {
                if (! is_string($languageCode) || $languageCode === '') {
                    continue;
                }

                TranslationValue::query()->updateOrCreate(
                    [
                        'translation_string_id' => $translationString->id,
                        'language_code' => $languageCode,
                    ],
                    [
                        'value' => $value,
                        'is_approved' => true,
                    ],
                );
            }

            $updated++;
        }

        return $updated;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function jsonRows(string $contents): array
    {
        $payload = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        return $payload['translations'] ?? $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function csvRows(string $contents): array
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contents);
        rewind($handle);

        $headers = fgetcsv($handle);

        if ($headers === false) {
            return [];
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = collect($headers)
                ->mapWithKeys(fn (string $header, int $index): array => [
                    $header => $row[$index] ?? null,
                ])
                ->all();
        }

        return $rows;
    }
}
