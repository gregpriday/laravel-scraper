<?php

namespace GregPriday\Scraper;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Scraper
{
    protected Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? app('scraper.client');
    }

    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->client->get($url, $options);
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
