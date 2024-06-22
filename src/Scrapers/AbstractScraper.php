<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperInterface;
use GregPriday\Scraper\Contracts\ScraperResponseInterface;
use Psr\Http\Message\RequestInterface;

abstract class AbstractScraper implements ScraperInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    abstract public function scrape(string $url, array $options = []): ScraperResponseInterface;

    abstract public function transformRequest(RequestInterface $request, array $options = []): RequestInterface;

    abstract public function transformResponse(mixed $response): ScraperResponseInterface;

    abstract public function getName(): string;
}
