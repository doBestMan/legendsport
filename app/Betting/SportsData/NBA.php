<?php

namespace App\Betting\SportsData;

use App\Betting\Pagination;
use App\Betting\SingleEventUpdater;
use App\Betting\Sport;
use App\Betting\SportsData\OddsFilters\HasOddsFromChosenSportsbook;
use App\Betting\SportsData\OddsFilters\MainLines;
use App\Domain\ApiEvent;

class NBA extends AbstractSportsData implements SingleEventUpdater
{
    public const PROVIDER_NAME = "sportsdata.io/nba";
    public const PROVIDER_DESCRIPTION = 'SportsData.io NBA';
    public const SPORT_ID = '10001';

    public function getEvents(int $page): Pagination
    {
        $data = $this->get('https://api.sportsdata.io/v3/nba/odds/json/BettingEvents/2020');
        $results = $this->parseEvents($data);
        $count = count($results);
        return new Pagination($results, $count, $count);
    }

    public function getResults(): array
    {
        $data = $this->get('https://api.sportsdata.io/v3/nba/odds/json/BettingEvents/2020');
        return $this->parseResults($data);
    }

    public function getSports(): array
    {
        return [new Sport(static::SPORT_ID, 'Basketball', self::PROVIDER_NAME)];
    }

    public function updateEventOdds(ApiEvent $apiEvent): void
    {
        $key = $apiEvent->getApiId();

        $results = $this->get(sprintf('https://api.sportsdata.io/v3/nba/odds/json/BettingMarkets/%s', $key));

        $this->logger->info(sprintf('Retrieving odds for events: %s', $key));

        $preMatchOdds = $this->parser->parseMainLines(
            new MainLines(new HasOddsFromChosenSportsbook(new \ArrayIterator($results)))
        );

        $apiEvent->updateOdds($preMatchOdds);
    }
}
