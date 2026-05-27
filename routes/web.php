<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\EnrollmentCreateController;
use App\Http\Controllers\EnrollmentStoreController;
use App\Http\Controllers\FleetIndexController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorIndexController;
use App\Http\Controllers\KnowledgeArticleController;
use App\Http\Controllers\KnowledgeBaseIndexController;
use App\Http\Controllers\LocaleSwitchController;
use App\Http\Controllers\ProgramCategoryController;
use App\Http\Controllers\ReviewIndexController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->post('/language', LocaleSwitchController::class)
    ->name('locale.switch');

Route::middleware(['web'])
    ->prefix('')
    ->as('site.')
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/apply', EnrollmentCreateController::class)->name('apply');
        Route::post('/apply', EnrollmentStoreController::class)->name('apply.store');
        Route::get('/categories/{trainingProgram:slug}', ProgramCategoryController::class)->name('categories.show');
        Route::get('/instructors', InstructorIndexController::class)->name('instructors');
        Route::get('/fleet', FleetIndexController::class)->name('fleet');
        Route::get('/reviews', ReviewIndexController::class)->name('reviews');
        Route::get('/blog', KnowledgeBaseIndexController::class)->name('blog.index');
        Route::get('/blog/{knowledgeArticle:slug}', KnowledgeArticleController::class)->name('blog.show');
        Route::get('/contacts', ContactController::class)->name('contacts');
        Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
        Route::get('/robots.txt', RobotsController::class)->name('robots');
    });
