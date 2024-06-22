<?php

namespace GregPriday\Scraper\Tests;

use GregPriday\Scraper\Http\ScraperClientFactory;

class MiddlewareTest extends TestCase
{
    public function test_fetch_with_middleware()
    {
        $client = ScraperClientFactory::create();
        $response = $client->get('https://books.toscrape.com/catalogue/tipping-the-velvet_999/index.html');

        dd($response->getBody()->getContents());
    }
}
