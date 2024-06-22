<?php

namespace GregPriday\Scraper\Contracts;

use Psr\Http\Message\RequestInterface;

interface ScraperInterface
{
    public function scrape(string $url, array $options = []): ScraperResponseInterface;
    public function transformRequest(RequestInterface $request, array $options = []): RequestInterface;
    public function transformResponse(mixed $response): ScraperResponseInterface;
    public function getName(): string;
}
