<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractScraper implements ScraperInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    abstract public function scrape(string $url, array $options = []): ResponseInterface;

    abstract public function transformRequest(RequestInterface $request, array $options = []): RequestInterface;

    abstract public function transformResponse(ResponseInterface $response): ResponseInterface;

    abstract public function getName(): string;
}
