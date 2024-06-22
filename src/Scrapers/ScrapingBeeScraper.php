<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScrapingBeeScraper extends AbstractScraper implements ScraperInterface
{
    protected Client $client;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->client = new Client([
            'base_uri' => 'https://app.scrapingbee.com/api/v1/',
            'timeout' => 140,
        ]);
    }

    public function transformRequest(RequestInterface $request, array $options = []): Request
    {
        $url = $request->getUri()->__toString();
        return $this->buildRequest($url, $options);
    }

    protected function buildRequest(string $url, array $options = []): Request
    {
        $params = [
            'api_key' => $this->config['api_key'],
            'url' => $url,
            'render_js' => 'true',
            'premium_proxy' => 'false',
        ];

        // Merge any additional options
        $params = array_merge($params, $options);

        $uri = 'https://app.scrapingbee.com/api/v1/?' . http_build_query($params);

        return new Request('GET', $uri, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function transformResponse(ResponseInterface $response, string $url = null): ResponseInterface
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();
        $headers = $response->getHeaders();

        // Extract the resolved URL from the 'Spb-Resolved-Url' header
        $resolvedUrl = $response->getHeaderLine('Spb-resolved-url') ?? $url ?? '';

        // Add the resolved URL as a custom header
        $headers['X-Resolved-Url'] = [$resolvedUrl];

        // Create a new PSR-7 Response object
        return new Response($statusCode, $headers, $body);
    }

    public function getName(): string
    {
        return 'scrapingbee';
    }
}
