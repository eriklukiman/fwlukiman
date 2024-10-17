<?php declare(strict_types=1);
namespace Lukiman\tests;

use Lukiman\Cores\Utils\Data;
use PHPUnit\Framework\TestCase;

final class DataTest extends TestCase {
    public function testInterpolation() : void {
        $this->assertEquals(5, Data::interpolation(5, 1, 1, 10, 10));
        $this->assertEquals(5, Data::interpolation(5, 1, 1, 10, 10.0));
        $this->assertEquals(5.0, Data::interpolation(5, 1, 1, 10, 10));
        $this->assertEquals(5.0, Data::interpolation(5, 1, 1, 10, 10.0));
        $this->assertEquals(5, Data::interpolation(5, 1, 1.0, 10, 10));
        $this->assertEquals(5, Data::interpolation(5, 1, 1.0, 10, 10.0));
        $this->assertEquals(5.0, Data::interpolation(5, 1, 1.0, 10, 10));
        $this->assertEquals(5.0, Data::interpolation(5, 1, 1.0, 10, 10.0));
        $this->assertEquals(5.5, Data::interpolation(5.5, 1, 1, 10, 10));
        $this->assertEquals(5.5, Data::interpolation(5.5, 1, 1, 10, 10.0));
        $this->assertEquals(5.5, Data::interpolation(5.5, 1, 1.0, 10, 10));
        $this->assertEquals(5.5, Data::interpolation(5.5, 1, 1.0, 10, 10.0));
        $this->assertEquals(75, Data::interpolation(7.5, 0, 0, 10, 100));
        $this->assertEquals(25, Data::interpolation(7.5, 0, 100, 10, 0));
    }

    public function testExtrapolation() : void {
        $this->assertEquals(5, Data::extrapolation(5, 1, 1, 10, 10));
        $this->assertEquals(5, Data::extrapolation(5, 1, 1, 10, 10.0));
        $this->assertEquals(5.0, Data::extrapolation(5, 1, 1, 10, 10));
        $this->assertEquals(5.0, Data::extrapolation(5, 1, 1, 10, 10.0));
        $this->assertEquals(5, Data::extrapolation(5, 1, 1.0, 10, 10));
        $this->assertEquals(5, Data::extrapolation(5, 1, 1.0, 10, 10.0));
        $this->assertEquals(5.0, Data::extrapolation(5, 1, 1.0, 10, 10));
        $this->assertEquals(5.0, Data::extrapolation(5, 1, 1.0, 10, 10.0));
        $this->assertEquals(5.5, Data::extrapolation(5.5, 1, 1, 10, 10));
        $this->assertEquals(5.5, Data::extrapolation(5.5, 1, 1, 10, 10.0));
        $this->assertEquals(5.5, Data::extrapolation(5.5, 1, 1.0, 10, 10));
        $this->assertEquals(5.5, Data::extrapolation(5.5, 1, 1.0, 10, 10.0));
        $this->assertEquals(75, Data::extrapolation(7.5, 0, 0, 10, 100));
        $this->assertEquals(25, Data::extrapolation(7.5, 0, 100, 10, 0));

        $this->assertEquals(11, Data::extrapolation(11, 1, 1, 10, 10));
        $this->assertEquals(110, Data::extrapolation(11, 0, 0, 10, 100));
        $this->assertEquals(-10, Data::extrapolation(11, 0, 100, 10, 0));
        $this->assertEqualsWithDelta(111.11111, Data::extrapolation(0, 1, 100, 10, 0), 0.0001);
    }
}
