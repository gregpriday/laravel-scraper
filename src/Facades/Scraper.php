<?php

namespace GregPriday\Scraper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \GregPriday\Scraper\Scraper
 *
 * @method static \Psr\Http\Message\ResponseInterface get(string $url, array $options = [])
 * @method static \GuzzleHttp\Client getClient()
 */
class Scraper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \GregPriday\Scraper\Scraper::class;
    }
}
