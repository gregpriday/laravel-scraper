<?php

namespace GregPriday\Scraper;

use GregPriday\Scraper\Http\ScraperClientFactory;
use GregPriday\Scraper\Scrapers\ScrapingBeeScraper;
use GregPriday\Scraper\Scrapers\ZyteScraper;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScraperServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-scraper')
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->singleton(ScraperManager::class, function ($app) {
            $manager = new ScraperManager($app);

            // Register ScrapingBee scraper
            // If there is an API key in the config, add the scraper
            if ($app->config['scraper.scrapers.scrapingbee.api_key']) {
                $scrapingBeeScraper = new ScrapingBeeScraper($app->config['scraper.scrapers.scrapingbee']);
                $manager->addScraper($scrapingBeeScraper);
            }

            // Register Zyte scraper
            if ($app->config['scraper.scrapers.zyte.api_key']) {
                $zyteScraper = new ZyteScraper($app->config['scraper.scrapers.zyte']);
                $manager->addScraper($zyteScraper);
            }

            // If there are no scrapers, throw an exception
            if (empty($manager->getScrapers())) {
                throw new \Exception('No scrapers have been configured');
            }

            return $manager;
        });

        $this->app->singleton('scraper.client', function ($app) {
            return ScraperClientFactory::create(
                $app->config['scraper.http'],
                $app->make(ScraperManager::class)
            );
        });

        $this->app->singleton(Scraper::class, function ($app) {
            return new Scraper($app->make('scraper.client'));
        });
    }

    public function provides(): array
    {
        return [
            'scraper.client',
            ScraperManager::class,
        ];
    }
}
