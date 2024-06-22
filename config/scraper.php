<?php

// config for GregPriday/Scraper
return [
    'scrapers' => [
        'scrapingbee' => [
            'api_key' => env('SCRAPING_BEE_API_KEY'),
            'base_uri' => 'https://app.scrapingbee.com/api/v1/',
        ],
        'zyte' => [
            'api_key' => env('ZYTE_API_KEY'),
            'base_uri' => 'https://app.zyte.com/api/v2/',
        ],
    ],
    'http' => [
        'timeout' => 10,
    ],
];
