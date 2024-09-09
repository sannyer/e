<?php

declare(strict_types=1);

namespace App\Services;

use App\DataObjects\SubmitDateTime;
use App\DataObjects\TurnaroundTime;
use DateTimeImmutable;


class DueDateCalculatorService
{

  private string $workingHoursStart;
  private string $workingHoursEnd;
  private array $workingDays;

  public function __construct()
  {
    $this->workingHoursStart = config('emarsys.working_hours_start');
    $this->workingHoursEnd = config('emarsys.working_hours_end');
    $this->workingDays = config('emarsys.working_days');
  }

  /**
   * Calculate the due date based on the submit date and time and the turnaround time.
   *
   * @param SubmitDateTime $submitDateTime
   * @param TurnaroundTime $turnaroundTime
   * @return SubmitDateTime // the return type guarantees that the returned value is a valid time in working hours on a working day
   */
  public function calculateDueDate(SubmitDateTime $submitDateTime, TurnaroundTime $turnaroundTime): SubmitDateTime
  {
    $dateTimeStart = new DateTimeImmutable($this->workingHoursStart);
    $dateTimeEnd = new DateTimeImmutable($this->workingHoursEnd);
    $startHour = (int)$dateTimeStart->format('H');
    $startMinute = (int)$dateTimeStart->format('i');
    $endHour = (int)$dateTimeEnd->format('H');
    $endMinute = (int)$dateTimeEnd->format('i');

    $minutesLeft = $turnaroundTime->getMinutes();

    // this will move gradually from starting point to the due date
    $cursor = $submitDateTime->getDateTime();

    // we can safely assume that the starting point is a working day on a working hour
    // because otherwise the inputs would've resulted in an exception
    while ($minutesLeft > 0 || !$this->isWorkingDay($cursor)) {
      if ($this->isWorkingDay($cursor)) {
        $dayEnd = $cursor->setTime($endHour, $endMinute);
        $minutesToDayEnd = $this->minutesDiff($cursor, $dayEnd);
        if ($minutesLeft < $minutesToDayEnd) {
          $cursor = $cursor->modify('+ ' . $minutesLeft . ' minutes');
          $minutesLeft = 0;
          break;
        }
        $minutesLeft -= $minutesToDayEnd;
      }
      $cursor = $cursor->modify('+ 1 day')->setTime($startHour, $startMinute);
    }

    $cursorDateTime = $cursor->format('Y-m-d H:i:s');

    return new SubmitDateTime($cursorDateTime);
  }

  protected function isWorkingDay(DateTimeImmutable $date): bool
  {
    return in_array($date->format('N'), $this->workingDays);
  }

  protected function isWorkingHour(DateTimeImmutable $date): bool
  {
    return $date->format('H:i') >= $this->workingHoursStart && $date->format('H:i') <= $this->workingHoursEnd;
  }

  public function calculateWorkdayMinutes(): int
  {
    $start = new DateTimeImmutable($this->workingHoursStart);
    $end = new DateTimeImmutable($this->workingHoursEnd);
    return $this->minutesDiff($start, $end);
  }

  protected function minutesDiff(DateTimeImmutable $start, DateTimeImmutable $end): int
  {
    $secDiff = $end->getTimestamp() - $start->getTimestamp();
    $minDiff = (int) floor($secDiff / 60);
    return $minDiff;
  }
}
