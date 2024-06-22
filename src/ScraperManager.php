<?php

namespace GregPriday\Scraper;

use GregPriday\Scraper\Contracts\ScraperInterface;
use GregPriday\Scraper\Scrapers\ScrapingBeeScraper;
use GregPriday\Scraper\Scrapers\ZyteScraper;
use Illuminate\Support\Manager;

class ScraperManager extends Manager
{
    protected $scrapers = [];

    protected $nextPriority = 0;

    public function addScraper(ScraperInterface $scraper, ?int $priority = null)
    {
        if ($priority === null) {
            $priority = $this->nextPriority;
            $this->nextPriority--;
        }

        $this->scrapers[] = [
            'scraper' => $scraper,
            'priority' => $priority,
        ];

        $this->sortScrapers();

        $this->drivers[$scraper->getName()] = $scraper;
    }

    protected function sortScrapers()
    {
        usort($this->scrapers, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    public function getScrapers(): array
    {
        return array_column($this->scrapers, 'scraper');
    }

    public function addScrapingBeeDriver(array $config): ScrapingBeeScraper
    {
        $scraper = new ScrapingBeeScraper($config);
        $this->addScraper($scraper, $config['priority'] ?? null);

        return $scraper;
    }

    public function addZyteDriver(array $config): ZyteScraper
    {
        $scraper = new ZyteScraper($config);
        $this->addScraper($scraper, $config['priority'] ?? null);

        return $scraper;
    }

    public function getDefaultDriver()
    {
        return $this->config['scraper.default'];
    }
}
