<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperInterface;
use GregPriday\Scraper\Contracts\ScraperResponseInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;

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

    public function scrape(string $url, array $options = []): ScraperResponseInterface
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

            $data = json_decode($response->getBody(), true);

            return $this->transformResponse($data);
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

    public function transformResponse(mixed $response): ScraperResponseInterface
    {
        // Create and return a ScraperResponseInterface implementation
        // You'll need to create this class (e.g., ZyteScraperResponse)
        return new ZyteScraperResponse(
            $response['browserHtml'] ?? '',
            $response['statusCode'] ?? 200,
            $response['url'] ?? '',
            $response['httpResponseHeaders'] ?? []
        );
    }

    public function getName(): string
    {
        return 'zyte';
    }
}
