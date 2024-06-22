# Laravel Scraper

Laravel Scraper is a flexible and powerful web scraping package for Laravel applications. It provides a unified interface to interact with multiple scraping services, allowing you to easily switch between or combine different scraping APIs based on your needs.

## Key Features

- **Multiple Scraper Support**: Integrate various scraping services like ScrapingBee, Zyte, and more.
- **Prioritized Scraping**: Configure the order in which different scraping services are attempted.
- **Unified Interface**: Use a single, consistent API to interact with all supported scraping services.
- **Request Transformation**: Automatically transform requests to match the requirements of each scraping service.
- **Response Normalization**: Convert diverse API responses into a standardized format for easy handling.
- **Middleware Integration**: Seamlessly integrates with Laravel's HTTP client, including support for retries and custom middleware.
- **Extensibility**: Easily add new scraping services by implementing the `ScraperInterface`.

## How It Works

1. **Scraper Stack**: The package maintains a prioritized stack of scraping services.
2. **Request Processing**: When a scraping request is made, the package attempts to use each scraper in the stack, starting with the highest priority.
3. **Request Transformation**: Before sending a request to a scraping service, the package transforms the request to match the service's specific API requirements.
4. **Response Handling**: After receiving a response, the package normalizes it into a consistent format, including setting a custom `X-Resolved-Url` header.
5. **Fallback Mechanism**: If a scraper fails, the package automatically tries the next one in the stack until successful or all scrapers have been attempted.

## Key Components

- **ScraperManager**: Manages the stack of scrapers and their priorities.
- **Scraper**: The main class that orchestrates the scraping process across multiple services.
- **ScraperInterface**: Defines the contract that all scraper implementations must follow.
- **AbstractScraper**: A base class that provides common functionality for scraper implementations.
- **ScraperMiddleware**: Integrates the scraping functionality into Laravel's HTTP client.
- **ScraperResponseInterface**: Defines a consistent structure for scraper responses.

## Use Cases

- Web scraping with automatic fallback to more reliable (but potentially more expensive) services.
- Distributing scraping loads across multiple services to avoid rate limits.
- Easily switching between or testing different scraping services in your application.

By providing a flexible, extensible, and easy-to-use interface for web scraping, Laravel Scraper simplifies the process of integrating and managing multiple scraping services in your Laravel applications.

## Creating a Scraper

To add a new scraping service to Laravel Scraper, you need to create a new class that implements the `ScraperInterface` or extends the `AbstractScraper` class. Here's a step-by-step guide to creating a custom scraper:

1. Create a new class in the `Scrapers` directory:

```php
<?php

namespace GregPriday\Scraper\Scrapers;

use GregPriday\Scraper\Contracts\ScraperResponseInterface;
use GregPriday\Scraper\Responses\CustomScraperResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Client;

class CustomScraper extends AbstractScraper
{
    protected $client;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->client = new Client([
            'base_uri' => $this->config['api_url'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['api_key'],
            ],
        ]);
    }

    public function scrape(string $url, array $options = []): ScraperResponseInterface
    {
        $response = $this->client->get('scrape', [
            'query' => array_merge(['url' => $url], $options),
        ]);

        return $this->transformResponse($response);
    }

    public function transformRequest(RequestInterface $request, array $options = []): RequestInterface
    {
        // Transform the original request to match your scraping service's API
        // For example, you might need to change the method, add headers, or modify the URL
        return $request->withHeader('X-Custom-Header', 'CustomValue');
    }

    public function transformResponse(mixed $response): ScraperResponseInterface
    {
        $data = json_decode($response->getBody(), true);
        $content = $data['content'] ?? '';
        $statusCode = $data['status_code'] ?? 200;
        $headers = $data['headers'] ?? [];

        // Always include the X-Resolved-Url header
        $headers['X-Resolved-Url'] = $data['final_url'] ?? $data['requested_url'] ?? '';

        return new CustomScraperResponse($content, $statusCode, $headers);
    }

    public function getName(): string
    {
        return 'custom_scraper';
    }
}
```

2. Implement the required methods:
    - `scrape(string $url, array $options = [])`: Performs the actual scraping operation.
    - `transformRequest(RequestInterface $request, array $options = [])`: Modifies the original request to match your scraping service's API requirements.
    - `transformResponse(mixed $response)`: Converts the API response into a standardized `ScraperResponseInterface` object.
    - `getName()`: Returns a unique identifier for this scraper.

3. Create a corresponding Response class:

```php
<?php

namespace GregPriday\Scraper\Responses;

use GregPriday\Scraper\Contracts\ScraperResponseInterface;

class CustomScraperResponse implements ScraperResponseInterface
{
    protected $content;
    protected $statusCode;
    protected $headers;

    public function __construct(string $content, int $statusCode, array $headers)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getBody(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
```

4. Register your new scraper in the `ScraperManager`:

```php
use GregPriday\Scraper\Scrapers\CustomScraper;

// In your service provider or wherever you configure the ScraperManager
$manager->addScraper(new CustomScraper($config), $priority);
```

### Important Conventions

- Always set the `X-Resolved-Url` header in the `transformResponse` method. This header should contain the final URL after any redirects.
- Ensure that your scraper handles errors gracefully and throws appropriate exceptions when scraping fails.
- Use the `$options` array in the `scrape` method to allow users to pass additional parameters to your scraping service.
- In the `transformRequest` method, make sure to preserve any important headers or options from the original request that may be needed for the scraping operation.

By following these steps and conventions, you can create a custom scraper that integrates seamlessly with the Laravel Scraper package, maintaining consistency with other scrapers and providing a reliable scraping experience.
