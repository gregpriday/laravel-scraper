<?php

namespace GregPriday\Scraper\Http;

use GregPriday\Scraper\Http\Middleware\ScraperMiddleware;
use GregPriday\Scraper\ScraperManager;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

class ScraperClientFactory
{
    public static function create(array $config = null, ScraperManager $manager = null): Client
    {
        $config = $config ?? config('scraper.http');
        $manager = $manager ?? app(ScraperManager::class);

        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory([
            'retry_on_status' => [403, 429, 500, 502, 503, 504],
            'max_retry_attempts' => 3,
            'retry_on_timeout' => true,
        ]));
        $stack->push(new ScraperMiddleware($manager));

        $config = array_merge($config, ['handler' => $stack]);

        return new Client($config);
    }
}
