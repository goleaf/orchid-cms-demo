<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Payment;
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
        return 'Payments';
    }

    public function description(): ?string
    {
        return 'Student payments, payment methods, and finance status.';
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
                TD::make('paid_at', 'Paid at')
                    ->render(fn (Payment $payment): string => $payment->paid_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('student', 'Student')
                    ->render(fn (Payment $payment): string => $payment->studentProfile->fullName()),
                TD::make('program', 'Program')
                    ->render(fn (Payment $payment): string => $payment->enrollment?->trainingProgram?->title ?? '-'),
                TD::make('amount_cents', 'Amount')
                    ->render(fn (Payment $payment): string => $payment->amountForHumans()),
                TD::make('method', 'Method')
                    ->render(fn (Payment $payment): string => str($payment->method)->replace('_', ' ')->title()->toString()),
                TD::make('status', 'Status')
                    ->render(fn (Payment $payment): string => str($payment->status->value)->replace('_', ' ')->title()->toString()),
                TD::make('reference', 'Reference')
                    ->render(fn (Payment $payment): string => $payment->reference ?? '-'),
            ]),
        ];
    }
}
