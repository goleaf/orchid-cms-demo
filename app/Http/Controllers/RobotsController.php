<?php

namespace App\Http\Controllers;

use App\Actions\GenerateRobotsTxtAction;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(GenerateRobotsTxtAction $robots): Response
    {
        return response($robots->handle(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
