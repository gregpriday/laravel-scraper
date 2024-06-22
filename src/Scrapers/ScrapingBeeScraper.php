<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperInterface;
use GregPriday\Scraper\Contracts\ScraperResponseInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;

class ScrapingBeeScraper extends AbstractScraper implements ScraperInterface
{
    protected $client;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->client = new Client([
            'base_uri' => 'https://app.scrapingbee.com/api/v1/',
            'timeout' => 140,
        ]);
    }

    public function scrape(string $url, array $options = []): ScraperResponseInterface
    {
        $params = [
            'api_key' => $this->config['api_key'],
            'url' => $url,
            'render_js' => 'true',
            'premium_proxy' => 'false',
        ];

        // Merge any additional options
        $params = array_merge($params, $options);

        try {
            $response = $this->client->get('', [
                'query' => $params,
            ]);

            return $this->transformResponse($response);
        } catch (GuzzleException $e) {
            // Handle the exception (log it, throw a custom exception, etc.)
            throw new \Exception('ScrapingBee scraping failed: '.$e->getMessage());
        }
    }

    public function transformRequest(RequestInterface $request, array $options = []): RequestInterface
    {
        // ScrapingBee doesn't require request transformation
        return $request;
    }

    public function transformResponse(mixed $response): ScraperResponseInterface
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();
        $headers = $response->getHeaders();

        // Extract the resolved URL from the 'Spb-Resolved-Url' header
        $resolvedUrl = $headers['Spb-Resolved-Url'][0] ?? '';

        // Create and return a ScraperResponseInterface implementation
        return new ScrapingBeeScraperResponse($body, $statusCode, $resolvedUrl, $headers);
    }

    public function getName(): string
    {
        return 'scrapingbee';
    }
}
