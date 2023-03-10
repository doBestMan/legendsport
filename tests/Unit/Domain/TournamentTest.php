<?php

namespace Unit\Domain;

use App\Betting\Settlement;
use App\Betting\SportEvent\Line;
use App\Betting\SportEvent\Result;
use App\Betting\TimeStatus;
use App\Domain\BetItem;
use App\Domain\BetPlacementException;
use App\Domain\BetTypes\MoneyLineAway;
use App\Domain\BetTypes\SpreadAway;
use App\Domain\BetTypes\TotalOver;
use App\Domain\Tournament;
use App\Domain\TournamentBet;
use App\Domain\TournamentPlayer;
use App\Domain\User;
use App\Tournament\Enums\TournamentState;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Tests\Fixture\Factory\ApiEventFactory;
use Tests\Fixture\Factory\FactoryAbstract;

/**
 * @covers \App\Domain\Tournament
 * @covers \App\Domain\BetPlacementException
 * @uses \App\Domain\User
 * @uses \App\Domain\TournamentPlayer
 * @uses \App\Domain\BetItem
 * @uses \App\Domain\Prizes\PrizeMoney
 * @uses \App\Domain\Prizes\PrizeStructure
 * @uses \App\Domain\Prizes\Prize
 * @uses \App\Domain\Prizes\PrizeCollection
 * @uses \App\Domain\Prizes\PrizeMoneyCollection
 */
class TournamentTest extends TestCase
{
    public function testPlaceStraightBet()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();

        $player = $user->getTournamentPlayer($tournament);

        $tournament->placeStraightBet($player, 500, new BetItem('moneyline_away', $event));

        self::assertEquals(1, $tournament->getBets()->count());

        /** @var TournamentBet $bet */
        $bet = $tournament->getBets()->first();

        self::assertEquals(500, $bet->getChipsWager());
        self::assertSame($player, $bet->getTournamentPlayer());
        self::assertEquals(1, $bet->getEvents()->count());
        self::assertEquals(9500, $player->getChips());
    }

    public function testPlaceStraightBetInTournamentWithStartedEvents()
    {
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        FactoryAbstract::setProperty($tournament, 'state', TournamentState::RUNNING());
        $tournament->registerPlayer($user);
        $tournament->addEvent(ApiEventFactory::createStartedEvent());
        $tournament->addEvent(ApiEventFactory::create());
        $event = $tournament->getBettableEvents()->first();

        $player = $user->getTournamentPlayer($tournament);

        $tournament->placeStraightBet($player, 500, new BetItem('moneyline_away', $event));

        self::assertEquals(1, $tournament->getBets()->count());

        /** @var TournamentBet $bet */
        $bet = $tournament->getBets()->first();

        self::assertEquals(500, $bet->getChipsWager());
        self::assertSame($player, $bet->getTournamentPlayer());
        self::assertEquals(1, $bet->getEvents()->count());
    }

    public function testPlaceStraightBetNotRegistered()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = new TournamentPlayer($tournament, $user, 10000);

        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::notRegistered()->getMessage());
        $tournament->placeStraightBet($player, 500, new BetItem('moneyline_away', $event));
    }

    public function testPlaceStraightBetTournamentOver()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = $user->getTournamentPlayer($tournament);

        FactoryAbstract::setProperty($tournament, 'state', TournamentState::COMPLETED());
        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::tournamentOver()->getMessage());
        $tournament->placeStraightBet($player, 500, new BetItem('moneyline_away', $event));
    }

    public function testPlaceStraightBetEventStarted()
    {
        $apiEvent = ApiEventFactory::create();

        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = $user->getTournamentPlayer($tournament);

        FactoryAbstract::setProperty($apiEvent, 'timeStatus', TimeStatus::ENDED());
        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::eventStarted()->getMessage());
        $tournament->placeStraightBet($player, 500, new BetItem('moneyline_away', $event));
    }

    public function testPlaceStraightBetInvalidEvent()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = $user->getTournamentPlayer($tournament);

        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::invalidEvent()->getMessage());
        $tournament->placeStraightBet($player, 500, new BetItem('moneyline_away', clone $event));
    }

    public function testPlaceParlayBet()
    {
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent(ApiEventFactory::create());
        $event = $tournament->getBettableEvents()->first();
        FactoryAbstract::setProperty($event, 'id', 1);

        $player = $user->getTournamentPlayer($tournament);

        $tournament->placeParlayBet(
            $player, 500,
            new BetItem('moneyline_away', $event),
            new BetItem('total_over', $event)
        );

        self::assertEquals(1, $tournament->getBets()->count());

        /** @var TournamentBet $bet */
        $bet = $tournament->getBets()->first();

        self::assertEquals(500, $bet->getChipsWager());
        self::assertSame($player, $bet->getTournamentPlayer());
        self::assertEquals(2, $bet->getEvents()->count());
        self::assertEquals(9500, $player->getChips());
    }

    public function testPlaceParlayBetInTournamentWithStartedEvent()
    {
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        FactoryAbstract::setProperty($tournament, 'state', TournamentState::RUNNING());
        $tournament->registerPlayer($user);
        $tournament->addEvent(ApiEventFactory::create());
        $tournament->addEvent(ApiEventFactory::createStartedEvent());
        $event = $tournament->getBettableEvents()->first();
        FactoryAbstract::setProperty($event, 'id', 1);

        $player = $user->getTournamentPlayer($tournament);

        $tournament->placeParlayBet(
            $player, 500,
            new BetItem('moneyline_away', $event),
            new BetItem('total_over', $event)
        );

        self::assertEquals(1, $tournament->getBets()->count());

        /** @var TournamentBet $bet */
        $bet = $tournament->getBets()->first();

        self::assertEquals(500, $bet->getChipsWager());
        self::assertSame($player, $bet->getTournamentPlayer());
        self::assertEquals(2, $bet->getEvents()->count());
    }

    public function testPlaceParlayBetNotRegistered()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = new TournamentPlayer($tournament, $user, 10000);

        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::notRegistered()->getMessage());
        $tournament->placeParlayBet(
            $player, 500,
            new BetItem('moneyline_away', $event),
            new BetItem('total_over', $event)
        );
    }

    public function testPlaceParlayBetTournamentOver()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = $user->getTournamentPlayer($tournament);

        FactoryAbstract::setProperty($tournament, 'state', TournamentState::COMPLETED());
        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::tournamentOver()->getMessage());
        $tournament->placeParlayBet(
            $player, 500,
            new BetItem('moneyline_away', $event),
            new BetItem('total_over', $event)
        );
    }

    public function testPlaceParlayBetEventStarted()
    {
        $apiEvent = ApiEventFactory::create();

        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = $user->getTournamentPlayer($tournament);

        FactoryAbstract::setProperty($apiEvent, 'timeStatus', TimeStatus::ENDED());
        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::eventStarted()->getMessage());
        $tournament->placeParlayBet(
            $player, 500,
            new BetItem('moneyline_away', $event),
            new BetItem('total_over', $event)
        );
    }

    public function testPlaceParlayBetInvalidEvent()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = $user->getTournamentPlayer($tournament);

        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::invalidEvent()->getMessage());
        $tournament->placeParlayBet(
            $player, 500,
            new BetItem('moneyline_away', clone $event),
            new BetItem('total_over', $event)
        );
    }

    public function testPlaceParlayBetCorrelatedEvents()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        FactoryAbstract::setProperty($event, 'id', 1);
        $player = $user->getTournamentPlayer($tournament);

        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::correlatedEvents()->getMessage());
        $tournament->placeParlayBet(
            $player, 500,
            new BetItem('moneyline_away', $event),
            new BetItem('spread_away', $event)
        );
    }

    public function testPlaceParlayBetInsufficientEvents()
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        $tournament->registerPlayer($user);
        $tournament->addEvent($apiEvent);
        $event = $tournament->getBettableEvents()->first();
        $player = $user->getTournamentPlayer($tournament);

        $this->expectException(BetPlacementException::class);
        $this->expectExceptionMessage(BetPlacementException::insufficientEvents()->getMessage());
        $tournament->placeParlayBet(
            $player, 500,
            new BetItem('moneyline_away', $event)
        );
    }

    /** @dataProvider provideReadyForCompletion */
    public function testIsReadyForCompletion(Result $result, bool $evaluateBet, bool $autoend, bool $expectedResult)
    {
        $apiEvent = ApiEventFactory::create();
        $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        FactoryAbstract::setProperty($tournament, 'autoEnd', $autoend);
        $tournament->addEvent($apiEvent);
        $tournament->registerPlayer($user);
        $event = $tournament->getBettableEvents()->first();
        $player = $user->getTournamentPlayer($tournament);
        $tournament->placeStraightBet($player, 1000, new BetItem('moneyline_away', $event));
        $bet = $event->getBets()->first();

        $apiEvent->result($result);

        if ($evaluateBet) {
            $apiEvent->updateLines(new Line('moneyline::away::fulltime', 200, null, Settlement::WON()));
            $bet->evaluate();
        }

        self::assertEquals($expectedResult, $tournament->isReadyForCompletion());
    }

    public function provideReadyForCompletion()
    {
        $preMatch = new Result(
            'eid',
            'test',
            TimeStatus::NOT_STARTED(),
            '2020-10-01 18:08:00',
            null,
            null,
            null,
            null
        );

        $finished = new Result(
            'eid',
            'test',
            TimeStatus::ENDED(),
            '2020-10-01 18:08:00',
            1,
            3,
            null,
            null
        );

        return [
            [$preMatch, false, true, false],
            [$preMatch, true, true, false],
            [$finished, false, true, false],
            [$finished, true, true, true],
            [$finished, true, false, false],
        ];
    }

    /** @dataProvider provideGetPrizePoolMoney */
    public function testGetPrizePoolMoney(array $prizePool, int $buyIn, int $players, int $expected)
    {
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        FactoryAbstract::setProperty($tournament, 'prizePool', $prizePool);
        FactoryAbstract::setProperty($tournament, 'buyIn', $buyIn);

        for ($i = 0; $i < $players; $i++) {
            $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
            $tournament->registerPlayer($user);
        }

        self::assertEquals($expected, $tournament->getPrizePoolMoney());
    }

    public function provideGetPrizePoolMoney()
    {
        return [
            [['type' => 'Fixed', 'fixed_value' => 100000], 1000, 15, 100000],
            [['type' => 'Auto', 'fixed_value' => 100000], 1000, 15, 15000],
            [['type' => 'Fixed', 'fixed_value' => 100000], 10000, 15, 100000],
        ];
    }

    public function testGetPrizePoolMoneyInvalid()
    {
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        FactoryAbstract::setProperty($tournament, 'prizePool', ['type' => 'Boosted', 'boost_value' => 100000]);

        $this->expectException(\UnexpectedValueException::class);

        $tournament->getPrizePoolMoney();
    }

    public function testGetPrizeMoney()
    {
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        FactoryAbstract::setProperty($tournament, 'prizePool', ['type' => 'Fixed', 'fixed_value' => 100000]);

        for ($i = 0; $i < 5; $i++) {
            $user = new User('test', 'test@test.com', 'test', '', '', new \DateTime());
            $tournament->registerPlayer($user);
        }

        self::assertCount(3, $tournament->getPrizeMoney()->toArray());
    }

    /** @dataProvider provideIsFinished */
    public function testIsFinished(TournamentState $state, bool $expected)
    {
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        FactoryAbstract::setProperty($tournament, 'state', $state);

        self::assertEquals($expected, $tournament->isFinished());
    }

    public function provideIsFinished()
    {
        return [
            [TournamentState::RUNNING(), false],
            [TournamentState::CANCELED(), true],
            [TournamentState::COMPLETED(), true],
            [TournamentState::REGISTERING(), false],
            [TournamentState::ANNOUNCED(), false],
            [TournamentState::LATE_REGISTERING(), false],
        ];
    }

    public function testComplete()
    {
        $tournament = new Tournament();
        FactoryAbstract::setProperty($tournament, 'id', 1);
        FactoryAbstract::setProperty($tournament, 'chips', 10000);
        FactoryAbstract::setProperty($tournament, 'state', TournamentState::RUNNING());

        $tournament->complete();

        self::assertEquals(TournamentState::COMPLETED(), $tournament->getState());
        self::assertEquals(true, $tournament->isFinished());
        self::assertEqualsWithDelta(Carbon::now(), $tournament->getCompletedAt(), 2);
        self::assertEqualsWithDelta(Carbon::now(), $tournament->getUpdatedAt(), 2);
    }
}
