<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ZyteScraper extends AbstractScraper implements ScraperInterface
{
    protected $client;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->client = new Client([
            'base_uri' => $this->config['base_uri'],
            'headers' => [
                'Authorization' => 'Basic '.base64_encode($this->config['api_key'].':'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function scrape(string $url, array $options = []): ResponseInterface
    {
        $payload = [
            'url' => $url,
            'browserHtml' => true,
        ];

        // Merge any additional options
        $payload = array_merge($payload, $options);

        try {
            $response = $this->client->post('extract', [
                'json' => $payload,
            ]);

            return $this->transformResponse($response);
        } catch (GuzzleException $e) {
            // Handle the exception (log it, throw a custom exception, etc.)
            throw new \Exception('Zyte scraping failed: '.$e->getMessage());
        }
    }

    public function transformRequest(RequestInterface $request, array $options = []): RequestInterface
    {
        // For Zyte, we don't need to transform the request
        // as we're using a separate client for API calls
        return $request;
    }

    public function transformResponse(ResponseInterface $response): ResponseInterface
    {
        $data = json_decode((string) $response->getBody(), true);

        $body = $data['browserHtml'] ?? '';
        $statusCode = $data['statusCode'] ?? 200;
        $headers = $data['httpResponseHeaders'] ?? [];

        // Add the resolved URL as a custom header
        $headers['X-Resolved-Url'] = [$data['url'] ?? ''];

        // Create a new PSR-7 Response object
        return new Response($statusCode, $headers, $body);
    }

    public function getName(): string
    {
        return 'zyte';
    }
}
