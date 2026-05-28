<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\StudentDocument;
use App\Support\LocalizedLabel;
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
        return tkey('operations.documents.title');
    }

    public function description(): ?string
    {
        return tkey('operations.documents.description');
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
                TD::make('student', tkey('operations.columns.student'))
                    ->render(fn (StudentDocument $document): string => $document->studentProfile->fullName()),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (StudentDocument $document): string => $document->enrollment?->trainingProgram?->title ?? '-'),
                TD::make('document_type', tkey('operations.columns.type'))
                    ->render(fn (StudentDocument $document): string => LocalizedLabel::for('operations.document_types', $document->document_type)),
                TD::make('title', tkey('operations.columns.title'))
                    ->render(fn (StudentDocument $document): string => $document->title),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (StudentDocument $document): string => LocalizedLabel::for('operations.statuses.documents', $document->status)),
                TD::make('issued_at', tkey('operations.columns.issued'))
                    ->render(fn (StudentDocument $document): string => $document->issued_at?->toDateString() ?? '-'),
                TD::make('expires_at', tkey('operations.columns.expires'))
                    ->render(fn (StudentDocument $document): string => $document->expires_at?->toDateString() ?? '-'),
            ]),
        ];
    }
}
