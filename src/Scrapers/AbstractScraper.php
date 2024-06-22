<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractScraper implements ScraperInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $url, array $options = []): ResponseInterface
    {
        try {
            $request = $this->buildRequest($url, $options);
            $response = $this->client->send($request);

            return $this->transformResponse($response, $url);
        } catch (GuzzleException $e) {
            // Handle the exception (log it, throw a custom exception, etc.)
            throw new \Exception(self::class.' scraping failed: '.$e->getMessage());
        }
    }

    public function transformRequest(RequestInterface $request, array $options = []): Request
    {
        $url = $request->getUri()->__toString();

        return $this->buildRequest($url, $options);
    }

    abstract protected function buildRequest(string $url): Request;

    abstract public function transformResponse(ResponseInterface $response): ResponseInterface;

    abstract public function getName(): string;
}
