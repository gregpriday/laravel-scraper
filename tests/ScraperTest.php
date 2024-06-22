<?php

namespace GregPriday\Scraper\Tests;

use GregPriday\Scraper\ScraperManager;

class ScraperTest extends TestCase
{
    public function test_scrapingbee_scraper()
    {
        $scraper = $this->app->make(ScraperManager::class)->driver('zyte');
        $response = $scraper->get('https://books.toscrape.com/catalogue/tipping-the-velvet_999/index.html');
        dd($response->getBody()->getContents());
    }
}
