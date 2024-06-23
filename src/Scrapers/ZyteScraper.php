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

    protected function buildRequest(string $url): Request
    {
        $payload = array_merge([
            'url' => $url,
            'browserHtml' => true,
            'httpResponseHeaders' => true,
        ], $this->config['options'] ?? []);

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

    public function transformResponse(ResponseInterface $response, string $url): ResponseInterface
    {
        $data = json_decode((string) $response->getBody(), true);

        $body = $data['browserHtml'] ?? $data['httpResponseBody'] ?? '';
        $statusCode = $data['statusCode'] ?? 200;
        $rawHeaders = $data['httpResponseHeaders'] ?? [];

        // Transform the headers into the correct format
        $headers = $this->transformHeaders($rawHeaders);

        // Add the resolved URL as a custom header
        $headers['X-Resolved-Url'] = [$data['url'] ?? ''];

        // Create a new PSR-7 Response object
        return new Response($statusCode, $headers, $body);
    }

    protected function transformHeaders(array $rawHeaders): array
    {
        $headers = [];
        foreach ($rawHeaders as $header) {
            if (isset($header['name']) && isset($header['value'])) {
                $name = strtolower($header['name']);
                $value = $this->sanitizeHeaderValue($header['value']);

                if ($name === 'set-cookie') {
                    // Handle set-cookie headers separately
                    if (!isset($headers[$name])) {
                        $headers[$name] = [];
                    }
                    // Split multiple cookies and add them individually
                    $cookies = explode("\n", $value);
                    foreach ($cookies as $cookie) {
                        $headers[$name][] = trim($cookie);
                    }
                } elseif ($name === 'link') {
                    // Handle Link headers separately
                    if (!isset($headers[$name])) {
                        $headers[$name] = [];
                    }
                    // Split multiple link headers and add them individually
                    $links = explode(',', $value);
                    foreach ($links as $link) {
                        $headers[$name][] = trim($link);
                    }
                } else {
                    if (!isset($headers[$name])) {
                        $headers[$name] = [];
                    }
                    $headers[$name][] = $value;
                }
            }
        }

        return $headers;
    }

    protected function sanitizeHeaderValue(string $value): string
    {
        // Remove any character below ASCII 32 except \t
        $value = preg_replace('/[\x00-\x08\x0B-\x1F]/', '', $value);

        // Replace all whitespace characters with a single space
        $value = preg_replace('/\s+/', ' ', $value);

        // Trim leading and trailing whitespace
        return trim($value);
    }

    public function getName(): string
    {
        return 'zyte';
    }
}
