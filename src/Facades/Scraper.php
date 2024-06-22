<?php

namespace GregPriday\Scraper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \GregPriday\Scraper\Scraper
 */
class Scraper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \GregPriday\Scraper\Scraper::class;
    }
}
