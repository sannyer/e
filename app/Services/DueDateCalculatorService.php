<?php

declare(strict_types=1);

namespace App\Services;

use App\DataObjects\SubmitDateTime;
use App\DataObjects\TurnaroundTime;
use DateTimeImmutable;
use DateTime;


class DueDateCalculatorService
{

  protected string $workingHoursStart;
  protected string $workingHoursEnd;
  protected array $workingDays;

  public function __construct()
  {
    $this->workingHoursStart = config('emarsys.working_hours_start');
    $this->workingHoursEnd = config('emarsys.working_hours_end');
    $this->workingDays = array_map('intval', explode(',', config('emarsys.working_days')));
  }

  public function calculateDueDate(SubmitDateTime $submitDateTime, TurnaroundTime $turnaroundTime): SubmitDateTime
  {
    $cursor = new DateTime($submitDateTime->getDateTime()->format('Y-m-d H:i:s'));
    $minutesToAdd = $turnaroundTime->getMinutes();

    $dayWorkingMinutes = $this->calculateWorkdayMinutes();

    while ($minutesToAdd > 0) {
      if ($this->isWorkingDay($cursor)) {
        if ($this->isWorkingHour($cursor)) {
          $minutesToAdd -= $this->minutesDiff($cursor, $cursor->setTime(intval($this->workingHoursEnd)));
        }
      }
      while (!$this->isWorkingDay($cursor)) {
        $cursor->modify('+1 day');
      }


      $minutesToAdd -= $dayWorkingMinutes;
    }

    return new SubmitDateTime((string)$cursor);
  }

  protected function isWorkingDay(DateTime|DateTimeImmutable $date): bool
  {
    return in_array($date->format('N'), $this->workingDays);
  }

  protected function isWorkingHour(DateTime|DateTimeImmutable $date): bool
  {
    return $date->format('H:i') >= $this->workingHoursStart && $date->format('H:i') <= $this->workingHoursEnd;
  }

  protected function calculateWorkdayMinutes(): int
  {
    $start = new DateTimeImmutable($this->workingHoursStart);
    $end = new DateTimeImmutable($this->workingHoursEnd);
    return $this->minutesDiff($start, $end);
  }

  protected function minutesDiff(DateTime|DateTimeImmutable $start, DateTime|DateTimeImmutable $end): int
  {
    $startTs = new DateTimeImmutable($start->format('Y-m-d H:i:s'));
    $endTs = new DateTimeImmutable($end->format('Y-m-d H:i:s'));
    $secDiff = $endTs->getTimestamp() - $startTs->getTimestamp();
    return (int) floor($secDiff / 60);
  }
}
