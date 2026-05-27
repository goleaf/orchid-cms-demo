<?php

use App\Http\Controllers\BranchShowController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CallbackStoreController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentCreateController;
use App\Http\Controllers\EnrollmentStoreController;
use App\Http\Controllers\FleetIndexController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorIndexController;
use App\Http\Controllers\KnowledgeArticleController;
use App\Http\Controllers\KnowledgeBaseIndexController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LocaleSwitchController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProgramCategoryController;
use App\Http\Controllers\ReviewIndexController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SitePageController;
use App\Http\Controllers\ThankYouController;
use App\Http\Controllers\WebsiteLeadController;
use App\Http\Middleware\CaptureSiteTracking;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->post('/language', LocaleSwitchController::class)
    ->name('locale.switch');

Route::middleware(['web'])
    ->post('/language/switch', [LanguageController::class, 'switch'])
    ->name('website.language.switch');

Route::middleware(['web'])
    ->prefix('')
    ->as('site.')
    ->group(function (): void {
        Route::middleware([CaptureSiteTracking::class])
            ->group(function (): void {
                Route::get('/', HomeController::class)->name('home');
                Route::get('/apply', EnrollmentCreateController::class)->name('apply');
                Route::post('/apply', EnrollmentStoreController::class)->name('apply.store');
                Route::post('/callback', CallbackStoreController::class)->name('callback.store');
                Route::get('/courses/{trainingProgram:slug}', ProgramCategoryController::class)->name('courses.show');
                Route::get('/categories/{trainingProgram:slug}', ProgramCategoryController::class)->name('categories.show');
                Route::get('/prices', PricingController::class)->name('prices');
                Route::get('/branches/{branch:slug}', BranchShowController::class)->name('branches.show');
                Route::get('/contacts', ContactController::class)->name('contacts');
                Route::get('/thanks', ThankYouController::class)->name('thanks');
                Route::get('/instructors', InstructorIndexController::class)->name('instructors');
                Route::get('/fleet', FleetIndexController::class)->name('fleet');
                Route::get('/reviews', ReviewIndexController::class)->name('reviews');
                Route::get('/blog', KnowledgeBaseIndexController::class)->name('blog.index');
                Route::get('/blog/{knowledgeArticle:slug}', KnowledgeArticleController::class)->name('blog.show');
            });

        Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
        Route::get('/robots.txt', RobotsController::class)->name('robots');
    });

Route::middleware(['web', CaptureSiteTracking::class])
    ->as('website.')
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{trainingProgram:slug}', [CourseController::class, 'show'])->name('courses.show');
        Route::get('/pricing', PricingController::class)->name('pricing');
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/{branch:slug}', [BranchController::class, 'show'])->name('branches.show');
        Route::get('/contacts', ContactController::class)->name('contacts');
        Route::get('/thank-you', ThankYouController::class)->name('thank_you');
        Route::get('/pages/{sitePage:slug}', [SitePageController::class, 'show'])->name('pages.show');
        Route::post('/website/leads', [WebsiteLeadController::class, 'store'])->name('leads.store');
        Route::post('/website/callback', [WebsiteLeadController::class, 'callback'])->name('callback.store');
        Route::post('/website/contact', [WebsiteLeadController::class, 'contact'])->name('contact.store');
    });
