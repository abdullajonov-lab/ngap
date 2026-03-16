<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Jobs\Middleware;

use Closure;
use Illuminate\Contracts\Redis\LimiterTimeoutException;
use Illuminate\Support\Facades\Redis;

class RateLimitedBroadcast
{
    /**
     * @throws LimiterTimeoutException
     */
    public function handle(object $job, Closure $next): void
    {
        $rateLimit = (int) config('nutgram-admin-panel.broadcast.rate_limit', 25);

        Redis::throttle('nutgram:broadcast')
            ->allow($rateLimit)
            ->every(1)
            ->block(5)
            ->then(
                function () use ($job, $next) {
                    $next($job);
                },
                function () use ($job) {
                    $job->release(2);
                }
            );
    }
}
