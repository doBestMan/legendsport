<?php

namespace App\Betting;

use App\Betting\Lsports\Lsports;
use App\Domain\ApiEvent;
use Decimal\Decimal;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Facades\Http;

class LegendsOdds implements BettingProvider
{
    public const PROVIDER_NAME = "legends-odds";
    public const PROVIDER_DESCRIPTION = 'Legends Odds';

    private Lsports $lsports;
    private EntityManager $entityManager;

    public function __construct(Lsports $lsports, EntityManager $entityManager)
    {
        $this->lsports = $lsports;
        $this->entityManager = $entityManager;
    }

    public function getEvents(int $page): Pagination
    {
        $results = $this->get('https://odds.infra.qa.legendsports.bet/api/v1/all');
        $events = [];
        foreach ($results as $result) {
            if ($result['status'] !== 'upcoming') {
                continue;
            }
            $events[] = new SportEvent(
                $result['id'],
                $result['startDate'],
                $result['sportId'],
                $result['homeTeam'],
                $result['awayTeam'],
                self::PROVIDER_NAME
            );
        }

        usort($events, fn (SportEvent $a, SportEvent $b) => $a->getStartsAt() <=> $b->getStartsAt());

        $total = count($events);
        return new Pagination($events, $total, $total);
    }

    public function getOdds(bool $updatesOnly): array
    {
        $data = $this->get('https://odds.infra.qa.legendsports.bet/api/v1/all');
        $updates = [];

        foreach ($data as $item) {
            /** @var ApiEvent|null $apiEvent */
            $apiEvent = current($this->entityManager->getRepository(ApiEvent::class)->findBy([
                'apiId' => $item['id'],
                'provider' => static::PROVIDER_NAME,
            ])) ?: null;

            if ($apiEvent === null) {
                continue;
            }

            if ($apiEvent->isFinished()) {
                continue;
            }

            $sportsOdds= new SportEventOdd(
                $item['id'],
                decimal_to_american($item['moneylineHome']),
                decimal_to_american($item['moneylineAway']),
                decimal_to_american($item['spreadHome']),
                decimal_to_american($item['spreadAway']),
                $item['handicapHome'] ? new Decimal(explode(' ', $item['handicapHome'])[0]) : null,
                $item['handicapAway'] ? new Decimal(explode(' ', $item['handicapAway'])[0]) : null,
                decimal_to_american($item['over']),
                decimal_to_american($item['under']),
                $item['total'] ? new Decimal($item['total']) : null,
            );
            $apiEvent->updateOdds($sportsOdds);
            $updates[] = $apiEvent;
        }

        return $updates;
    }

    public function getResults(): array
    {
        $data = $this->get('https://odds.infra.qa.legendsports.bet/api/v1/all');

        $results = [];

        foreach($data as $item) {
            /** @var ApiEvent|null $apiEvent */
            $apiEvent = current($this->entityManager->getRepository(ApiEvent::class)->findBy([
                'apiId' => $item['id'],
                'provider' => static::PROVIDER_NAME,
            ])) ?: null;

            if ($apiEvent === null) {
                continue;
            }

            $results[] = new SportEventResult(
                $item['id'],
                self::PROVIDER_NAME,
                $this->mapTimeStatus($item['status']),
                $item['homeScore'],
                $item['awayScore']
            );
        }

        return $results;
    }

    public function getSports(): array
    {
        return $this->lsports->getSports();
    }

    private function get(string $url): array
    {
        $response = Http::get($url);

        $data = $response->json();

        if ($response->failed() || empty($data)) {
            //$this->logger->info('Unable to communicate with API', $data);
            throw new \RuntimeException('Unable to communicate with API');
        }

        return $data;
    }

    protected function mapTimeStatus(string $status): TimeStatus
    {
        switch ($status) {
            case 'upcoming':
                return TimeStatus::NOT_STARTED();
            case 'inplay':
                return TimeStatus::IN_PLAY();
            case 'ended':
                return TimeStatus::ENDED();
            case 'cancelled':
                return TimeStatus::CANCELED();
            default:
                return TimeStatus::IN_PLAY();
        }
    }
}
