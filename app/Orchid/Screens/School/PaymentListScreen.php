<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Payment;
use App\Support\LocalizedLabel;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PaymentListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'payments' => Payment::query()
                ->forPaymentList()
                ->with([
                    'studentProfile:id,first_name,last_name',
                    'enrollment:id,training_program_id',
                    'enrollment.trainingProgram:id,title',
                ])
                ->orderByDesc('paid_at')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return tkey('operations.payments.title');
    }

    public function description(): ?string
    {
        return tkey('operations.payments.description');
    }

    public function permission(): iterable
    {
        return ['platform.finance.payments'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('payments', [
                TD::make('paid_at', tkey('operations.columns.paid_at'))
                    ->render(fn (Payment $payment): string => $payment->paid_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('student', tkey('operations.columns.student'))
                    ->render(fn (Payment $payment): string => $payment->studentProfile->fullName()),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (Payment $payment): string => $payment->enrollment?->trainingProgram?->title ?? '-'),
                TD::make('amount_cents', tkey('operations.columns.amount'))
                    ->render(fn (Payment $payment): string => $payment->amountForHumans()),
                TD::make('method', tkey('operations.columns.method'))
                    ->render(fn (Payment $payment): string => LocalizedLabel::for('operations.payment_methods', $payment->method)),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (Payment $payment): string => LocalizedLabel::for('operations.statuses.payments', $payment->status)),
                TD::make('reference', tkey('operations.columns.reference'))
                    ->render(fn (Payment $payment): string => $payment->reference ?? '-'),
            ]),
        ];
    }
}
