<?php declare(strict_types=1);
namespace Lukiman\tests;

use Lukiman\Cores\Utils\SortedArray;
use PHPUnit\Framework\TestCase;

final class SortedArrayTest extends TestCase {
    public function testGetLowerValueExact() : void {
        $a = [2, 4, 6, 8, 10, 15, 20, 25, 30, 35];
        $this->assertEquals(4, SortedArray::getLowerValue(4, $a));
    }

    public function testGetLowerValue() : void {
        $a = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $this->assertEquals(5, SortedArray::getLowerValue(5, $a));
        $this->assertEquals(5, SortedArray::getLowerValue(5, $a, true));
        $this->assertEquals(4, SortedArray::getLowerValue(5, $a, false));
        $this->assertEquals(4, SortedArray::getLowerValue(4.5, $a));
        $this->assertEquals(4, SortedArray::getLowerValue(4.5, $a, true));
        $this->assertEquals(4, SortedArray::getLowerValue(4.5, $a, false));
        $this->assertEquals(1, SortedArray::getLowerValue(1, $a));
        $this->assertEquals(1, SortedArray::getLowerValue(1, $a, true));
        $this->assertNull(SortedArray::getLowerValue(1, $a, false));
        $this->assertNull(SortedArray::getLowerValue(0, $a));
        $this->assertNull(SortedArray::getLowerValue(0, $a, true));
        $this->assertNull(SortedArray::getLowerValue(0, $a, false));
        $this->assertEquals(10, SortedArray::getLowerValue(10, $a));
        $this->assertEquals(10, SortedArray::getLowerValue(10, $a, true));
        $this->assertEquals(9, SortedArray::getLowerValue(10, $a, false));
        $this->assertEquals(10, SortedArray::getLowerValue(11, $a));
        $this->assertEquals(10, SortedArray::getLowerValue(11, $a, true));
        $this->assertEquals(10, SortedArray::getLowerValue(11, $a, false));
    }

    public function testGetHigherValue() : void {
        $a = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $this->assertEquals(5, SortedArray::getHigherValue(5, $a));
        $this->assertEquals(5, SortedArray::getHigherValue(5, $a, true));
        $this->assertEquals(6, SortedArray::getHigherValue(5, $a, false));
        $this->assertEquals(6, SortedArray::getHigherValue(5.5, $a));
        $this->assertEquals(6, SortedArray::getHigherValue(5.5, $a, true));
        $this->assertEquals(6, SortedArray::getHigherValue(5.5, $a, false));
        $this->assertEquals(1, SortedArray::getHigherValue(1, $a));
        $this->assertEquals(1, SortedArray::getHigherValue(1, $a, true));
        $this->assertEquals(2, SortedArray::getHigherValue(1, $a, false));
        $this->assertEquals(1, SortedArray::getHigherValue(0, $a));
        $this->assertEquals(1, SortedArray::getHigherValue(0, $a, true));
        $this->assertEquals(1, SortedArray::getHigherValue(0, $a, false));
        $this->assertEquals(10, SortedArray::getHigherValue(10, $a));
        $this->assertEquals(10, SortedArray::getHigherValue(10, $a, true));
        $this->assertNull(SortedArray::getHigherValue(10, $a, false));
        $this->assertNull(SortedArray::getHigherValue(11, $a));
        $this->assertNull(SortedArray::getHigherValue(11, $a, true));
        $this->assertNull(SortedArray::getHigherValue(11, $a, false));
    }
}
