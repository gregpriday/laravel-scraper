<?php

namespace GregPriday\Scraper\Contracts;

interface ScraperResponseInterface
{
    public function getBody(): string;

    public function getStatusCode(): int;

    public function getHeaders(): array;

    public function getResolvedUrl(): string;
}
