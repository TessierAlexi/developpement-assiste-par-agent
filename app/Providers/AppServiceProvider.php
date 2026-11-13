<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Recall checks one user may ask for per minute. Opening a car asks for one,
     * and one that is not in the cache calls NHTSA.
     */
    protected const RECALL_CHECKS_PER_MINUTE = 30;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $this->configureRateLimiting();
    }

    /**
     * Limit how often each user may check the recalls of a car, with a message the page can show.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('recalls', fn (Request $request) => Limit::perMinute(self::RECALL_CHECKS_PER_MINUTE)
            ->by($request->user()?->getAuthIdentifier() ?: $request->ip())
            ->response(fn (Request $request, array $headers) => new JsonResponse(
                ['message' => 'Too many recall checks. Wait a minute and try again.'],
                Response::HTTP_TOO_MANY_REQUESTS,
                $headers,
            )));
    }
}
