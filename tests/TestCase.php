<?php

namespace GregPriday\Scraper\Tests;

use GregPriday\Scraper\ScraperClientFactory;
use GregPriday\Scraper\ScraperServiceProvider;
use GuzzleHttp\Client;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            ScraperServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }

    protected function getScraperClient($config = []): Client
    {
        $factory = new ScraperClientFactory();

        return $factory->make($config);
    }

    protected function assertValidResponse($response)
    {
        $this->assertNotNull($response);
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);
    }
}
