<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->prefix('')
    ->as('site.')
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');
    });
