<?php

namespace GregPriday\Scraper\Contracts;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ScraperInterface
{
    public function get(string $url, array $options = []): ResponseInterface;

    public function transformRequest(RequestInterface $request, array $options = []): RequestInterface;

    public function transformResponse(ResponseInterface $response): ResponseInterface;

    public function getName(): string;
}
