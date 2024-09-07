<?php

declare(strict_types=1);

namespace Tests\Unit\DataObjects;

use App\DataObjects\TurnaroundTime;
use InvalidArgumentException;
use Tests\TestCase;
use TypeError;

class TurnaroundTimeTest extends TestCase
{
    // Tests creation with a valid integer input
    public function testValidIntegerInput()
    {
        $turnaroundTime = new TurnaroundTime(120);
        $this->assertInstanceOf(TurnaroundTime::class, $turnaroundTime);
        $this->assertEquals(120, $turnaroundTime->getMinutes());
    }

    // Tests creation with a valid string input in HH:MM format
    public function testValidStringInputHHMM()
    {
        $turnaroundTime = new TurnaroundTime('2:30');
        $this->assertInstanceOf(TurnaroundTime::class, $turnaroundTime);
        $this->assertEquals(150, $turnaroundTime->getMinutes());
    }

    // Tests that an invalid string input throws an exception
    public function testInvalidStringInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid turnaround time format');
        new TurnaroundTime('invalid input');
    }

    // Tests that a negative input throws an exception
    public function testNegativeInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Turnaround time must be positive');
        new TurnaroundTime(-60);
    }

    // Tests that a zero input throws an exception
    public function testZeroInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Turnaround time must be positive');
        new TurnaroundTime(0);
    }

    // Tests the getHours() method
    public function testGetHours()
    {
        $turnaroundTime = new TurnaroundTime(150);
        $this->assertEquals(2.5, $turnaroundTime->getHours());
    }

    // Tests the __toString() method
    public function testToString()
    {
        $turnaroundTime = new TurnaroundTime(150);
        $this->assertEquals('2:30', (string)$turnaroundTime);
    }

    // Tests the equals() method
    public function testEquals()
    {
        $time1 = new TurnaroundTime(150);
        $time2 = new TurnaroundTime('2:30');
        $time3 = new TurnaroundTime(160);

        $this->assertTrue($time1->equals($time2));
        $this->assertFalse($time1->equals($time3));
    }

    // Tests the jsonSerialize() method
    public function testJsonSerialize()
    {
        $turnaroundTime = new TurnaroundTime(150);
        $this->assertEquals('"2:30"', json_encode($turnaroundTime));
    }

    // Tests creation with a large input value
    public function testLargeInput()
    {
        $turnaroundTime = new TurnaroundTime(1440); // 24 hours
        $this->assertEquals(1440, $turnaroundTime->getMinutes());
        $this->assertEquals('24:00', (string)$turnaroundTime);
    }

    // test float input resulting in an exception
    public function testFloatInput()
    {
        $this->expectException(TypeError::class);
        new TurnaroundTime(120.5);
    }
}
