<?php

declare(strict_types=1);

namespace Tests\Unit\DataObjects;

use App\DataObjects\SubmitDateTime;
use Tests\TestCase;
use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SubmitDateTimeTest extends TestCase
{

  private $originalConfig;

  protected function setUp(): void
  {
    parent::setUp();

    // Store the original configuration
    $this->originalConfig = [
      'working_hours_start' => config('emarsys.working_hours_start'),
      'working_hours_end' => config('emarsys.working_hours_end'),
      'timezone' => config('app.timezone'),
    ];

    // Set default test configuration
    Config::set('emarsys.working_hours_start', '09:00');
    Config::set('emarsys.working_hours_end', '17:00');
    Config::set('app.timezone', 'Europe/Budapest');
  }

  protected function tearDown(): void
  {
    // Reset to original configuration
    Config::set('emarsys.working_hours_start', $this->originalConfig['working_hours_start']);
    Config::set('emarsys.working_hours_end', $this->originalConfig['working_hours_end']);
    Config::set('app.timezone', $this->originalConfig['timezone']);

    parent::tearDown();
  }

  // test valid submit date time results in a valid object  
  public function testValidSubmitDateTime()
  {
    $dateTime = new SubmitDateTime('2023-05-15 10:30:00');
    $this->assertInstanceOf(SubmitDateTime::class, $dateTime);
  }

  // test invalid date format results in an exception
  public function testInvalidDateFormat()
  {
    $this->expectException(InvalidArgumentException::class);
    new SubmitDateTime('invalid-date-format');
  }

  // test submit time outside working hours results in an exception
  public function testSubmitTimeOutsideWorkingHours()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Submit time must be within working hours (09:00 - 17:00)');
    new SubmitDateTime('2023-05-15 18:00:00');
  }

  // test getDateTime returns a DateTimeImmutable object
  public function testGetDateTime()
  {
    $dateTime = new SubmitDateTime('2023-05-15 10:30:00');
    $this->assertInstanceOf(\DateTimeImmutable::class, $dateTime->getDateTime());
  }

  // test __toString returns a string in the correct format
  public function testToString()
  {
    $dateTime = new SubmitDateTime('2023-05-15 10:30:00');
    $this->assertEquals('2023-05-15T08:30:00Z', (string)$dateTime);
  }

  // test equals method comparing equal and non-equal objects
  public function testEquals()
  {
    $dateTime1 = new SubmitDateTime('2023-05-15 10:30:00');
    $dateTime2 = new SubmitDateTime('2023-05-15 10:30:00');
    $dateTime3 = new SubmitDateTime('2023-05-15 11:30:00');

    $this->assertTrue($dateTime1->equals($dateTime2));
    $this->assertFalse($dateTime1->equals($dateTime3));
  }

  // test JsonSerialize returns a string in the correct format
  public function testJsonSerialize()
  {
    $dateTime = new SubmitDateTime('2023-05-15 10:30:00');
    $this->assertEquals('"2023-05-15T08:30:00Z"', json_encode($dateTime));
  }

  // test lower boundary of working hours
  public function testLowerBoundaryOfWorkingHours()
  {
    $dateTime = new SubmitDateTime('2023-05-15 09:00:00');
    $this->assertInstanceOf(SubmitDateTime::class, $dateTime);
  }

  // test upper boundary of working hours
  public function testUpperBoundaryOfWorkingHours()
  {
    $dateTime = new SubmitDateTime('2023-05-15 16:59:59');
    $this->assertInstanceOf(SubmitDateTime::class, $dateTime);
  }

  // test different timezones return different timestamps with the same time
  public function testDifferentTimezones()
  {
    Config::set('app.timezone', 'America/New_York');
    $dateTime1 = new SubmitDateTime('2023-05-15 10:30:00');
    Config::set('app.timezone', 'Europe/Budapest');
    $dateTime2 = new SubmitDateTime('2023-05-15 10:30:00');

    $this->assertNotEquals($dateTime1->getDateTime(), $dateTime2->getDateTime());
  }

  // test daylight saving time transition (1 day difference results in only 23 hours difference due to DST)
  public function testDaylightSavingTimeTransition()
  {
    // Set a timezone that observes DST
    Config::set('app.timezone', 'America/New_York');

    // Test date before DST transition (2023-03-12 is when DST starts in the US).
    // Must use a 3-day (fri-mon) interval, because DST is always on weekend
    // and picking weekend dates would result validation error
    $beforeDST = new SubmitDateTime('2023-03-10 10:30:00');
    $afterDST = new SubmitDateTime('2023-03-13 10:30:00');

    // Ensure that both dates are created successfully
    $this->assertInstanceOf(SubmitDateTime::class, $beforeDST);
    $this->assertInstanceOf(SubmitDateTime::class, $afterDST);

    // Check if the time difference is exactly one hour
    // make sure the timestamp units match the calculations
    $timeDifference = $afterDST->getDateTime()->getTimestamp() - $beforeDST->getDateTime()->getTimestamp();
    $hour = 60 * 60;
    $day = 24 * $hour;
    $this->assertEquals(3 * $day - 1 * $hour, $timeDifference, "The time difference should be 23 hours due to DST transition");

    // Verify that the string representation is correct for both dates
    $this->assertEquals('2023-03-10T15:30:00Z', (string)$beforeDST);
    $this->assertEquals('2023-03-13T14:30:00Z', (string)$afterDST);
  }

  // test leap year
  public function testLeapYear()
  {
    // try with leap year and assert valid instance with February 29th
    $dateTime = new SubmitDateTime('2024-02-29 10:30:00');
    $this->assertInstanceOf(SubmitDateTime::class, $dateTime);
    $this->assertEquals('2024-02-29', (string)$dateTime->getDateTime()->format('Y-m-d'));
    // try with different year and expect resulting March 1st (where not weekend)
    $dateTime = new SubmitDateTime('2027-02-29 10:30:00');
    $this->assertInstanceOf(SubmitDateTime::class, $dateTime);
    $this->assertEquals('2027-03-01', (string)$dateTime->getDateTime()->format('Y-m-d'));
  }

  // test invalid working hours configuration
  public function testInvalidWorkingHoursConfiguration()
  {
    Config::set('emarsys.working_hours_start', '10:00');
    Config::set('emarsys.working_hours_end', '09:00');
    $this->expectException(InvalidArgumentException::class);
    new SubmitDateTime('2023-05-15 10:30:00');
  }

  // test submit time on weekend
  public function testSubmitTimeOnWeekend()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Submit date must be a working day');
    new SubmitDateTime('2023-05-14 10:30:00'); // This is a Sunday
  }
}
