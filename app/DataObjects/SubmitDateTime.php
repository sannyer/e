<?php

declare(strict_types=1);

namespace App\DataObjects;

use DateTimeImmutable;
use DateTimeZone;
use JsonSerializable;
use InvalidArgumentException;

final class SubmitDateTime implements JsonSerializable
{
  private DateTimeImmutable $dateTime;

  public function __construct(string $dateTimeString)
  {
    try {
      $this->dateTime = new DateTimeImmutable($dateTimeString, new DateTimeZone(config('app.timezone')));
      $this->dateTime->setTime(
        (int)$this->dateTime->format('H'),
        (int)$this->dateTime->format('i'),
        0
      );
      $this->validate();
    } catch (\Exception $e) {
      throw new InvalidArgumentException("Invalid submit date format: " . $e->getMessage());
    }
  }

  private function validate(): void
  {
    $workingHoursStart = config('emarsys.working_hours_start');
    $workingHoursEnd = config('emarsys.working_hours_end');
    // if any is not HH:MM format, throw an exception
    if (
      !preg_match('/^[0-2][0-9]:[0-5][0-9]$/', $workingHoursStart) ||
      !preg_match('/^[0-2][0-9]:[0-5][0-9]$/', $workingHoursEnd)
    ) {
      throw new InvalidArgumentException("Environment variables WORKING_HOURS_START and WORKING_HOURS_END must follow HH:MM format");
    }
    $submitTime = $this->dateTime->format('H:i');

    if ($submitTime < $workingHoursStart || $submitTime >= $workingHoursEnd) {
      throw new InvalidArgumentException(
        "Submit time must be within working hours ({$workingHoursStart} - {$workingHoursEnd})"
      );
    }
  }

  public function getDateTime(): DateTimeImmutable
  {
    return $this->dateTime;
  }

  public function __toString(): string
  {
    // Using UTC to make sure same time around the world is the same string
    // making it timezone agnostic
    // use a cloned object to avoid any issues
    $dt = clone $this->dateTime;
    return $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
  }

  // it's now timezone agnostic
  public function equals(SubmitDateTime $other): bool
  {
    return $this->__toString() === $other->__toString();
  }

  public function jsonSerialize(): mixed
  {
    return $this->__toString();
  }
}
