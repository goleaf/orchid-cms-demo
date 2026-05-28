<?php

namespace Tests;

use Closure;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Tabuna\Breadcrumbs\BreadcrumbsMiddleware;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->app->instance(BreadcrumbsMiddleware::class, new class
        {
            public function handle(Request $request, Closure $next): mixed
            {
                optional($request->route())->forgetParameter(BreadcrumbsMiddleware::class);

                return $next($request);
            }
        });
    }
}
