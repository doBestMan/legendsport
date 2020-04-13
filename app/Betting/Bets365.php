<?php
namespace App\Betting;

use App\Models\ApiEvent;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\SimpleCache\CacheInterface;

class Bets365 implements BettingProvider
{
    private const RESULTS_CACHE_TTL = 5 * 60;
    private const PREMATCH_CACHE_TTL = 12 * 60 * 60;

    const PROVIDER_NAME = "bet365";

    private Bets365API $api;
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache, Bets365API $api)
    {
        $this->api = $api;
        $this->cache = $cache;
    }

    public function getEvents(int $page): Pagination
    {
        // 151 - esport
        $sportId = "151";

        $data = $this->api->getUpcomingEvents($sportId, $page);

        $results = collect($data["results"])
            ->map(
                fn(array $item) => new SportEvent(
                    $item["id"],
                    (int) $item["time"],
                    $sportId,
                    $item["home"]["name"],
                    $item["away"]["name"],
                    static::PROVIDER_NAME,
                ),
            )
            ->all();

        return new Pagination($results, $data["pager"]["total"], $data["pager"]["per_page"]);
    }

    public function getOdds(): array
    {
        // Get all upcoming api events
        /** @var ApiEvent[]|Collection $apiEventDict */
        $apiEventDict = ApiEvent::query()
            ->where("provider", static::PROVIDER_NAME)
            ->where("time_status", TimeStatus::NOT_STARTED())
            ->get()
            ->mapWithKeys(fn(ApiEvent $apiEvent) => [$apiEvent->api_id => $apiEvent]);

        $preMatchOdds = collect();
        $eventsIdsToRequest = collect();

        $cacheKeys = $apiEventDict->map(
            fn(ApiEvent $apiEvent) => $this->createPreMatchKey($apiEvent->api_id),
        );
        $cachePreMatchOdds = collect($this->cache->getMultiple($cacheKeys));

        foreach ($apiEventDict as $apiEvent) {
            $cacheItem = $cachePreMatchOdds->get($this->createPreMatchKey($apiEvent->api_id));
            if ($cacheItem) {
                $preMatchOdds->push($cacheItem);
            } else {
                $eventsIdsToRequest->push($apiEvent->api_id);
            }
        }

        // Call API for data in 10-elements chunks
        foreach ($eventsIdsToRequest->chunk(10) as $keys) {
            $results = collect($this->api->getPreMatchOdds($keys->values()));
            $this->cache->setMultiple(
                $results->mapWithKeys(
                    fn(array $event) => [$this->createPreMatchKey($event['FI']) => $event],
                ),
                static::PREMATCH_CACHE_TTL,
            );
            $preMatchOdds->push(...$results);
        }

        // Map API items into SportEventOdds
        return collect($preMatchOdds)
            ->map(function (array $event) use ($apiEventDict) {
                $eventId = $event["FI"];
                $matchLine = Arr::get($event, "main.sp.match_lines") ?? [];

                /** @var ApiEvent $apiEvent */
                $apiEvent = $apiEventDict[$eventId];

                $moneyLineHome = null;
                $moneyLineAway = null;
                $pointSpreadHome = null;
                $pointSpreadAway = null;
                $pointSpreadHomeLine = null;
                $pointSpreadAwayLine = null;
                $overLine = null;
                $underLine = null;
                $totalNumber = null;

                foreach ($matchLine as $item) {
                    $teamName = Arr::get($item, "header");
                    $type = Arr::get($item, "name");
                    $odds = decimal_to_american(Arr::get($item, "odds"));
                    $handicap = as_decimal(Arr::get($item, "handicap"));

                    if ($type === "To Win" && $this->cmpStr($teamName, $apiEvent->team_home)) {
                        $moneyLineHome = $odds;
                    } elseif (
                        $type === "To Win" &&
                        $this->cmpStr($teamName, $apiEvent->team_away)
                    ) {
                        $moneyLineAway = $odds;
                    } elseif (
                        $type === "Handicap" &&
                        $this->cmpStr($teamName, $apiEvent->team_home)
                    ) {
                        $pointSpreadHome = $odds;
                        $pointSpreadHomeLine = $handicap;
                    } elseif (
                        $type === "Handicap" &&
                        $this->cmpStr($teamName, $apiEvent->team_away)
                    ) {
                        $pointSpreadAway = $odds;
                        $pointSpreadAwayLine = $handicap;
                    }
                }

                return new SportEventOdd(
                    $eventId,
                    $moneyLineHome,
                    $moneyLineAway,
                    $pointSpreadHome,
                    $pointSpreadAway,
                    $pointSpreadHomeLine,
                    $pointSpreadAwayLine,
                    $overLine,
                    $underLine,
                    $totalNumber,
                );
            })
            ->all();
    }

    public function getResults(): array
    {
        // Get all unfinished api events
        $apiEvents = ApiEvent::query()
            ->where("time_status", "<>", TimeStatus::ENDED())
            ->where("starts_at", "<=", Carbon::now())
            ->where("provider", static::PROVIDER_NAME)
            ->get();

        $eventsResults = collect();
        $eventsIdsToRequest = collect();

        $cacheKeys = $apiEvents->map(
            fn(ApiEvent $apiEvent) => $this->createResultKey($apiEvent->api_id),
        );
        $cacheEventsResults = collect($this->cache->getMultiple($cacheKeys));

        foreach ($apiEvents as $apiEvent) {
            $cacheItem = $cacheEventsResults->get($this->createResultKey($apiEvent->api_id));
            if ($cacheItem) {
                $eventsResults->push($cacheItem);
            } else {
                $eventsIdsToRequest->push($apiEvent->api_id);
            }
        }

        // Call API for data in 10-elements chunks
        foreach ($eventsIdsToRequest->chunk(10) as $keys) {
            $results = collect($this->api->getResults($keys->values()));
            $this->cache->setMultiple(
                $results->mapWithKeys(
                    fn(array $item) => [$this->createResultKey($item["bet365_id"]) => $item],
                ),
                static::RESULTS_CACHE_TTL,
            );
            $eventsResults->push(...$results);
        }

        // Map API items into SportEventResults
        return collect($eventsResults)
            ->filter(function (array $item) {
                $result = Arr::get($item, "ss");

                if (!$result) {
                    return false;
                }

                $exploded = explode("-", $item["ss"]);

                if (count($exploded) !== 2) {
                    return false;
                }

                return $item;
            })
            ->map(function ($item) {
                [$home, $away] = explode("-", $item["ss"]);
                return new SportEventResult(
                    $item["bet365_id"],
                    $home,
                    $away,
                    $this->mapTimeStatus($item["time_status"]),
                );
            })
            ->all();
    }

    public function getSports(): array
    {
        // https://betsapi.com/docs/GLOSSARY.html#r-sportid
        return [new Sport("1", "Soccer"), new Sport("151", "E-sports")];
    }

    public function createPreMatchKey(string $eventId): string
    {
        return "bets365.prematch.{$eventId}";
    }

    public function createResultKey(string $eventId): string
    {
        return "bets365.result.{$eventId}";
    }

    private function cmpStr(string $a, string $b): bool
    {
        return strtolower(trim($a)) === strtolower(trim($b));
    }

    private function mapTimeStatus(string $timeStatus): TimeStatus
    {
        // @see https://betsapi.com/docs/GLOSSARY.html
        switch ($timeStatus) {
            case "0":
                return TimeStatus::NOT_STARTED();
            case "1":
                return TimeStatus::IN_PLAY();
            //            case "3":
            //                return TimeStatus::ENDED();
            default:
                return TimeStatus::ENDED();
        }
    }
}
