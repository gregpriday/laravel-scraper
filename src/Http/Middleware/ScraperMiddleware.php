<?php

namespace GregPriday\Scraper\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GregPriday\Scraper\ScraperManager;
use GregPriday\Scraper\Exceptions\ScraperException;

class ScraperMiddleware
{
    protected $manager;

    public function __construct(ScraperManager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            // Throw an error for POST requests
            if ($request->getMethod() === 'POST') {
                throw new ScraperException("POST requests are not supported for scraping.");
            }

            $exceptions = [];
            foreach ($this->manager->getScrapers() as $scraper) {
                try {
                    $transformedRequest = $scraper->transformRequest($request, $options);
                    $response = $handler($transformedRequest, $options);

                    // Check if the response is successful
                    if ($this->isSuccessfulResponse($response)) {
                        return $scraper->transformResponse($response);
                    }

                    // If not successful, throw an exception to try the next scraper
                    throw new ScraperException("Scraper {$scraper->getName()} failed with status code: {$response->getStatusCode()}");
                } catch (\Exception $e) {
                    $exceptions[] = [
                        'scraper' => $scraper->getName(),
                        'exception' => $e->getMessage()
                    ];
                    // Log the exception or handle it as needed
                    // Continue to the next scraper
                }
            }

            // If all scrapers failed, throw a comprehensive exception
            throw new ScraperException("All scrapers failed to process the request. Details: " . json_encode($exceptions));
        };
    }

    protected function isSuccessfulResponse(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();
        return $statusCode >= 200 && $statusCode < 300;
    }
}
