<?php

namespace App\Orchid\Screens\School;

use App\Models\ExamAttemptStatus;
use App\Models\ExamResultStatus;
use App\Models\ExamStatus;
use App\Models\ExamType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

abstract class ExamDictionaryListScreen extends Screen
{
    /**
     * @return class-string<Model>
     */
    abstract protected function modelClass(): string;

    abstract protected function titleKey(): string;

    public function query(): iterable
    {
        $model = $this->modelClass();

        return [
            'records' => $model::query()
                ->when(method_exists($model, 'scopeForDictionaryList'), fn (Builder $query): Builder => $query->forDictionaryList())
                ->when(! method_exists($model, 'scopeForDictionaryList'), fn (Builder $query): Builder => $query->select($this->columnsForSelect()))
                ->orderBy('sort_order')
                ->orderBy('code')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey($this->titleKey());
    }

    public function description(): ?string
    {
        return tkey('menu.exams.settings');
    }

    public function permission(): iterable
    {
        return ['exams.dictionaries.manage'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('records', [
                TD::make('code', tkey('crm.dictionaries.fields.code'))
                    ->render(fn (Model $record): string => (string) $record->getAttribute('code')),
                TD::make('name', tkey('crm.dictionaries.fields.name'))
                    ->render(fn (Model $record): string => $this->recordName($record)),
                TD::make('is_internal', tkey('exams.fields.is_internal'))
                    ->render(fn (Model $record): string => $record instanceof ExamType ? $this->boolLabel($record->is_internal) : $this->dash()),
                TD::make('is_official', tkey('exams.fields.is_official'))
                    ->render(fn (Model $record): string => $record instanceof ExamType ? $this->boolLabel($record->is_official) : $this->dash()),
                TD::make('is_theory', tkey('exams.fields.is_theory'))
                    ->render(fn (Model $record): string => $record instanceof ExamType ? $this->boolLabel($record->is_theory) : $this->dash()),
                TD::make('is_practical', tkey('exams.fields.is_practical'))
                    ->render(fn (Model $record): string => $record instanceof ExamType ? $this->boolLabel($record->is_practical) : $this->dash()),
                TD::make('is_active', tkey('crm.dictionaries.fields.is_active'))
                    ->render(fn (Model $record): string => $this->boolLabel((bool) $record->getAttribute('is_active'))),
                TD::make('sort_order', tkey('crm.dictionaries.fields.sort_order'))
                    ->render(fn (Model $record): string => (string) ($record->getAttribute('sort_order') ?? $this->dash())),
            ]),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function columnsForSelect(): array
    {
        return [
            'id',
            'code',
            'name',
            'name_translations',
            'description_translations',
            'color',
            'sort_order',
            'is_system',
            'is_active',
        ];
    }

    private function recordName(Model $record): string
    {
        if ($record instanceof ExamType || $record instanceof ExamStatus || $record instanceof ExamAttemptStatus || $record instanceof ExamResultStatus) {
            return $record->displayName();
        }

        return (string) ($record->getAttribute('name') ?? $record->getAttribute('code'));
    }

    private function boolLabel(bool $value): string
    {
        return $value ? tkey('common.status.yes') : tkey('common.status.no');
    }

    private function dash(): string
    {
        return '-';
    }
}
