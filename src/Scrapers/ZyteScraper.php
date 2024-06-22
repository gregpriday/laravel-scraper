<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ZyteScraper extends AbstractScraper implements ScraperInterface
{
    protected Client $client;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->client = new Client([
            'base_uri' => $this->config['base_uri'],
            'headers' => [
                'Authorization' => 'Basic '.base64_encode($this->config['api_key'].':'),
                'Content-Type' => 'application/json',
            ],
            'timeout' => 300,
        ]);
    }

    protected function buildRequest(string $url, array $options = []): Request
    {
        $payload = array_merge([
            'url' => $url,
            'browserHtml' => true,
            'httpResponseHeaders' => true,
        ], $options);

        return new Request(
            'POST',
            $this->config['base_uri'].'extract',
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($this->config['api_key'].':'),
                'Accept-Encoding' => 'gzip',
            ],
            json_encode($payload)
        );
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
