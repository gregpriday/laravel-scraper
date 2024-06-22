<?php

// config for GregPriday/Scraper
return [
    'scrapers' => [
        'scrapingbee' => [
            'api_key' => env('SCRAPINGBEE_API_KEY'),
            'base_uri' => 'https://app.scrapingbee.com/api/v1/',
            'priority' => 0,
        ],
        'zyte' => [
            'api_key' => env('ZYTE_API_KEY'),
            'base_uri' => 'https://api.zyte.com/v1/',
            'priority' => 1,
        ],
    ],
    'http' => [
        'timeout' => 120,
        'retries' => env('SCRAPER_HTTP_RETRIES', 3),
    ],
];
