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
            $scrapingBeeScraper = new ScrapingBeeScraper($app->config['scraper.scrapers.scrapingbee']);
            $manager->addScraper($scrapingBeeScraper);

            // Register Zyte scraper
            $zyteScraper = new ZyteScraper($app['config']['scraper.scrapers.zyte']);
            $manager->addScraper($zyteScraper);

            return $manager;
        });

        $this->app->singleton('scraper.client', function ($app) {
            return ScraperClientFactory::create(
                $app->config['scraper.http'],
                $app->make(ScraperManager::class)
            );
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
