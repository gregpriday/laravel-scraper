<?php

namespace GregPriday\Scraper\Http\Middleware;

use GregPriday\Scraper\Exceptions\ScraperException;
use GregPriday\Scraper\ScraperManager;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScraperMiddleware
{
    protected $manager;

    private int $retries;

    public function __construct(ScraperManager $manager, int $retries = 1)
    {
        $this->manager = $manager;
        $this->retries = $retries;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($request->getMethod() === 'POST') {
                throw new ScraperException('POST requests are not supported for scraping.');
            }

            $exceptions = [];
            $scrapers = $this->manager->getScrapers();

            for ($try = 1; $try <= $this->retries; $try++) {
                foreach ($scrapers as $scraper) {
                    try {
                        $transformedRequest = $scraper->transformRequest($request, $options);

                        return $handler($transformedRequest, $options)
                            ->then(
                                function (ResponseInterface $response) use ($scraper) {
                                    if ($this->isSuccessfulResponse($response)) {
                                        return $scraper->transformResponse($response);
                                    }
                                    throw new ScraperException("Scraper {$scraper->getName()} failed with status code: {$response->getStatusCode()}");
                                }
                            )
                            ->otherwise(
                                function (\Exception $e) use ($scraper, &$exceptions, $try) {
                                    $exceptions[] = [
                                        'try' => $try,
                                        'scraper' => $scraper->getName(),
                                        'exception' => $e->getMessage(),
                                    ];
                                    throw $e;
                                }
                            );
                    } catch (\Exception $e) {
                        $exceptions[] = [
                            'try' => $try,
                            'scraper' => $scraper->getName(),
                            'exception' => $e->getMessage(),
                        ];

                        // Continue to the next scraper in this try
                        continue;
                    }
                }
                // If we've gone through all scrapers and haven't returned, move to the next try
            }

            // If all retries across all scrapers failed, throw a comprehensive exception
            throw new ScraperException('All scraper attempts failed. Details: '.json_encode($exceptions));
        };
    }

    protected function isSuccessfulResponse(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        return $statusCode >= 200 && $statusCode < 300;
    }
}
