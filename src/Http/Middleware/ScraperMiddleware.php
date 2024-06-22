<?php

namespace GregPriday\Scraper\Http\Middleware;

use GregPriday\Scraper\Exceptions\ScraperException;
use GregPriday\Scraper\ScraperManager;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScraperMiddleware
{
    protected ScraperManager $manager;

    protected int $maxRetries;

    public function __construct(ScraperManager $manager, int $maxRetries = 3)
    {
        $this->manager = $manager;
        $this->maxRetries = $maxRetries;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($request->getMethod() === 'POST') {
                throw new ScraperException('POST requests are not supported for scraping.');
            }

            $options['original_request'] = $request;
            $options['scraper_retry_count'] = 0;
            $options['scraper_index'] = 0;

            return $this->executeRequest($request, $options, $handler);
        };
    }

    protected function executeRequest(RequestInterface $request, array $options, callable $handler): Promise
    {
        $scrapers = $this->manager->getScrapers();
        $scraper = $scrapers[$options['scraper_index']];
        $transformedRequest = $scraper->transformRequest($request, $options);

        return $handler($transformedRequest, $options)->then(
            $this->onFulfilled($options, $handler),
            $this->onRejected($options, $handler)
        );
    }

    protected function onFulfilled(array $options, callable $handler): callable
    {
        return function (ResponseInterface $response) use ($options, $handler) {
            if ($this->shouldRetry($options, $response)) {
                return $this->doRetry($options, $handler);
            }

            return $this->transformResponse($response, $options);
        };
    }

    protected function onRejected(array $options, callable $handler): callable
    {
        return function ($reason) use ($options, $handler) {
            if ($reason instanceof RequestException && $this->shouldRetry($options, $reason->getResponse())) {
                return $this->doRetry($options, $handler);
            }

            throw $reason;
        };
    }

    protected function shouldRetry(array $options, ?ResponseInterface $response = null): bool
    {
        $scrapers = $this->manager->getScrapers();
        $totalAttempts = $options['scraper_retry_count'] * count($scrapers) + $options['scraper_index'] + 1;

        if ($totalAttempts >= $this->maxRetries * count($scrapers)) {
            return false;
        }

        if ($response) {
            $statusCode = $response->getStatusCode();

            return ($statusCode >= 500 && $statusCode < 600) || $statusCode === 429;
        }

        return false;
    }

    protected function doRetry(array $options, callable $handler): Promise
    {
        $scrapers = $this->manager->getScrapers();
        $options['scraper_index']++;

        if ($options['scraper_index'] >= count($scrapers)) {
            $options['scraper_index'] = 0;
            $options['scraper_retry_count']++;
        }

        return $this->executeRequest($options['original_request'], $options, $handler);
    }

    protected function transformResponse(ResponseInterface $response, array $options): ResponseInterface
    {
        $scrapers = $this->manager->getScrapers();
        $scraper = $scrapers[$options['scraper_index']];
        $url = (string) $options['original_request']->getUri();

        return $scraper->transformResponse($response, $url);
    }
}
