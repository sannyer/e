<?php

declare(strict_types=1);

namespace Tests\Unit\DataObjects;

use App\DataObjects\TurnaroundTime;
use InvalidArgumentException;
use Tests\TestCase;
use TypeError;
use Illuminate\Support\Facades\Log;

class TurnaroundTimeTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    // Tests creation with a valid integer input
    public function testValidIntegerInput()
    {
        $turnaroundTime = new TurnaroundTime(2);
        $this->assertInstanceOf(TurnaroundTime::class, $turnaroundTime);
        $this->assertEquals(120, $turnaroundTime->getMinutes());
    }

    // test float input resulting in an exception
    public function testValidFloatInput()
    {
        $turnaroundTime = new TurnaroundTime(1.5);
        $this->assertInstanceOf(TurnaroundTime::class, $turnaroundTime);
        $this->assertEquals(90, $turnaroundTime->getMinutes());
    }

    // test integer input coming as string
    public function testValidIntegerInputString()
    {
        $turnaroundTime = new TurnaroundTime('2');
        $this->assertInstanceOf(TurnaroundTime::class, $turnaroundTime);
        $this->assertEquals(120, $turnaroundTime->getMinutes());
    }

    // test valid float input coming as string
    public function testValidFloatInputString()
    {
        $turnaroundTime = new TurnaroundTime('1.5');
        $this->assertInstanceOf(TurnaroundTime::class, $turnaroundTime);
        $this->assertEquals(90, $turnaroundTime->getMinutes());
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
        new TurnaroundTime(-1);
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
        $turnaroundTime = new TurnaroundTime(5);
        $this->assertEquals(5, $turnaroundTime->getHours());
    }

    // Tests the __toString() method
    public function testToString()
    {
        $turnaroundTime = new TurnaroundTime(2.5);
        $this->assertEquals('2:30', (string)$turnaroundTime);
    }

    // Tests the equals() method
    public function testEquals()
    {
        $time1 = new TurnaroundTime(2.5);
        $time2 = new TurnaroundTime('2:30');
        $time3 = new TurnaroundTime(3);

        $this->assertTrue($time1->equals($time2));
        $this->assertFalse($time1->equals($time3));
    }

    // Tests the jsonSerialize() method
    public function testJsonSerialize()
    {
        $turnaroundTime = new TurnaroundTime(2.5);
        $this->assertEquals('"2:30"', json_encode($turnaroundTime));
    }

    // Tests creation with a large input value
    public function testLargeInput()
    {
        $turnaroundTime = new TurnaroundTime(24); // 24 hours
        $this->assertEquals(1440, $turnaroundTime->getMinutes());
        $this->assertEquals('24:00', (string)$turnaroundTime);
    }
}
