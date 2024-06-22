<?php

namespace GregPriday\Scraper\Http\Middleware;

use GregPriday\Scraper\Exceptions\ScraperException;
use Psr\Http\Message\RequestInterface;

/**
 * Class ValidateScraperRequestMiddleware
 *
 * A middleware that ensures the request is always going through a scraper.
 */
class ValidateScraperRequestMiddleware
{
    /**
     * @var string[]
     */
    private array $allowedHosts;

    public function __construct()
    {
        // For all config('scraper.scrapers'), look at `base_uri` to get the allowed hosts
        $this->allowedHosts = collect(config('scraper.scrapers'))
            ->map(fn ($scraper) => parse_url($scraper['base_uri'], PHP_URL_HOST))
            ->toArray();
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $requestHost = parse_url($request->getUri(), PHP_URL_HOST);

            if (! in_array($requestHost, $this->allowedHosts)) {
                throw new ScraperException('Host not allowed for scraping.');
            }

            return $handler($request, $options);
        };
    }
}
