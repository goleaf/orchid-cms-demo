<?php

namespace App\Http\Controllers;

use App\Actions\GetPublicReviewsAction;
use Illuminate\Contracts\View\View;

class ReviewIndexController extends Controller
{
    public function __invoke(GetPublicReviewsAction $reviews): View
    {
        return view('site.reviews', $reviews->handle());
    }
}
