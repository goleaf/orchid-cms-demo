<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\StudentDocument;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class DocumentListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'documents' => StudentDocument::query()
                ->forDocumentList()
                ->with([
                    'studentProfile:id,first_name,last_name',
                    'enrollment:id,training_program_id',
                    'enrollment.trainingProgram:id,title',
                ])
                ->orderBy('expires_at')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Documents';
    }

    public function description(): ?string
    {
        return 'Identity, medical, contract, and exam document tracking.';
    }

    public function permission(): iterable
    {
        return ['platform.documents'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('documents', [
                TD::make('student', 'Student')
                    ->render(fn (StudentDocument $document): string => $document->studentProfile->fullName()),
                TD::make('program', 'Program')
                    ->render(fn (StudentDocument $document): string => $document->enrollment?->trainingProgram?->title ?? '-'),
                TD::make('document_type', 'Type')
                    ->render(fn (StudentDocument $document): string => str($document->document_type)->replace('_', ' ')->title()->toString()),
                TD::make('title', 'Title')
                    ->render(fn (StudentDocument $document): string => $document->title),
                TD::make('status', 'Status')
                    ->render(fn (StudentDocument $document): string => str($document->status->value)->replace('_', ' ')->title()->toString()),
                TD::make('issued_at', 'Issued')
                    ->render(fn (StudentDocument $document): string => $document->issued_at?->toDateString() ?? '-'),
                TD::make('expires_at', 'Expires')
                    ->render(fn (StudentDocument $document): string => $document->expires_at?->toDateString() ?? '-'),
            ]),
        ];
    }
}
