<?php

namespace Unit\Http\Transformers\App;

use App\Http\Transformers\App\ApiEventToOdds;
use Decimal\Decimal;
use PHPUnit\Framework\TestCase;
use Tests\Fixture\Factory\ApiEventFactory;

/**
 * @covers \App\Http\Transformers\App\ApiEventToOdds
 * @uses \App\Domain\ApiEvent
 * @uses \App\Domain\ApiEventOdds
 */
class ApiEventToOddsTest extends TestCase
{
    public function testTransform()
    {
        $expected = [
            "external_id" => 'eid',
            "moneyline_away" => -200,
            "moneyline_home" => 200,
            "point_spread_away" => -125,
            "point_spread_home" => 275,
            "point_spread_away_line" => new Decimal('-2'),
            "point_spread_home_line" => new Decimal('2'),
            "overline" => 150,
            "underline" => -175,
            "total_number" => new Decimal('4'),
        ];
        $apiEvent = ApiEventFactory::create();
        $sut = new ApiEventToOdds();

        $result = $sut->transform($apiEvent);

        self::assertEquals($expected, $result);
        self::assertEquals('-2', $result['point_spread_away_line']->toString());
        self::assertEquals('2', $result['point_spread_home_line']->toString());
        self::assertEquals('4', $result['total_number']->toString());
    }
}
