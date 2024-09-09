<?php

declare(strict_types=1);

namespace App\DataObjects;

use InvalidArgumentException;
use JsonSerializable;

final class TurnaroundTime implements JsonSerializable
{
  private int $minutes;

  const REGEX_TIME = '/^[0-9]+:[0-9]+$/';

  public function __construct(string|int $turnaroundTime)
  {
    $this->minutes = $this->parseInput($turnaroundTime);
    $this->validate();
  }

  private function parseInput(string|int $turnaroundTime): int
  {
    if (is_numeric($turnaroundTime)) {
      return (int)$turnaroundTime; // the integer is considered as minutes
    }

    // if HH:MM format, convert to minutes
    if (preg_match(self::REGEX_TIME, $turnaroundTime)) {
      list($hours, $minutes) = explode(':', $turnaroundTime);
      return ((int)$hours * 60) + (int)$minutes;
    }

    // if none of the above, throw an exception
    throw new InvalidArgumentException("Invalid turnaround time format");
  }

  private function validate(): void
  {
    if ($this->minutes <= 0) {
      throw new InvalidArgumentException("Turnaround time must be positive");
    }
  }

  public function getMinutes(): int
  {
    return $this->minutes;
  }

  public function getHours(): float
  {
    return $this->minutes / 60;
  }

  public function __toString(): string
  {
    $hours = floor($this->minutes / 60);
    $remainingMinutes = $this->minutes % 60;
    return sprintf("%d:%02d", $hours, $remainingMinutes);
  }

  public function equals(TurnaroundTime $other): bool
  {
    return $this->minutes === $other->minutes;
  }

  public function jsonSerialize(): mixed
  {
    return $this->__toString();
  }
}
