# Laravel Scraper

Laravel Scraper is a flexible and powerful web scraping package for Laravel applications. It provides a unified interface to interact with multiple scraping services, allowing you to easily switch between or combine different scraping APIs based on your needs.

## Installation

You can install the package via composer:

```bash
composer require gregpriday/laravel-scraper
```

## Configuration

After installation, publish the configuration file:

```bash
php artisan vendor:publish --provider="GregPriday\Scraper\ScraperServiceProvider"
```

This will create a `config/scraper.php` file where you can configure your scraping services.

## Configuring Scraping Services

Laravel Scraper supports multiple scraping services. Here's how to configure some popular ones:

### ScrapingBee

1. Sign up for an account at [ScrapingBee](https://www.scrapingbee.com/)
2. Get your API key from the dashboard
3. Add the following to your `.env` file:

```
SCRAPINGBEE_API_KEY=your_api_key_here
```

### Zyte (formerly Scrapy Cloud)

1. Create an account at [Zyte](https://www.zyte.com/)
2. Obtain your API key
3. Add to your `.env` file:

```
ZYTE_API_KEY=your_api_key_here
```

## Basic Usage

Here's how to make a basic scraping request:

```php
use GregPriday\Scraper\Facades\Scraper;

$response = Scraper::get('https://example.com');

$content = $response->getBody();
$statusCode = $response->getStatusCode();
$headers = $response->getHeaders();
```
## Using with Spatie Crawler

Laravel Scraper can be easily integrated with [Spatie's Crawler](https://github.com/spatie/crawler). Here's a quick example:

```php
use Spatie\Crawler\Crawler;
use GregPriday\Scraper\Facades\Scraper;

Crawler::create()
    ->setCrawlObserver(YourCrawlObserver::class)
    ->setClient(Scraper::getClient())
    ->startCrawling('https://example.com');
```

This sets up the crawler to use Laravel Scraper for all requests, benefiting from its multi-service capabilities and automatic retries.

## Advanced Configuration

You can add or modify scraping services in the `config/scraper.php` file. Each service can have its own configuration and priority:

```php
return [
    'scrapers' => [
        'scrapingbee' => [
            'driver' => 'scrapingbee',
            'api_key' => env('SCRAPINGBEE_API_KEY'),
            'priority' => 10,
        ],
        'zyte' => [
            'driver' => 'zyte',
            'api_key' => env('ZYTE_API_KEY'),
            'priority' => 20,
        ],
        // Add more scrapers here
    ],
];
```

The `priority` determines the order in which scrapers are attempted, with lower numbers being tried first.

## Creating Custom Scrapers

You can create custom scrapers by implementing the `ScraperInterface` or extending the `AbstractScraper` class.

## Error Handling

Laravel Scraper will automatically try the next scraper in the stack if one fails. You can catch exceptions at the application level:

```php
use GregPriday\Scraper\Exceptions\ScraperException;

try {
    $response = Scraper::get('https://example.com');
} catch (ScraperException $e) {
    // Handle the exception
}
```

## License

The Laravel Scraper package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
