<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DataObjects\SubmitDateTime;
use App\DataObjects\TurnaroundTime;
use App\Services\DueDateCalculatorService;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class DueDateCalculatorServiceTest extends TestCase
{
    private DueDateCalculatorService $service;

    private array $originalConfig;

    protected function setUp(): void
    {
        parent::setUp();
        // store original config values
        $this->originalConfig = [
            'emarsys.working_hours_start' => Config::get('emarsys.working_hours_start'),
            'emarsys.working_hours_end' => Config::get('emarsys.working_hours_end'),
            'emarsys.working_days' => Config::get('emarsys.working_days'),
            'app.timezone' => Config::get('app.timezone'),
        ];

        // Set default test configuration
        Config::set('emarsys.working_hours_start', '09:00');
        Config::set('emarsys.working_hours_end', '17:00');
        Config::set('emarsys.working_days', [1, 2, 3, 4, 5]); // Monday to Friday
        Config::set('app.timezone', 'UTC');

        $this->service = new DueDateCalculatorService();
    }

    protected function tearDown(): void
    {
        // Restore original config values
        Config::set('emarsys.working_hours_start', $this->originalConfig['emarsys.working_hours_start']);
        Config::set('emarsys.working_hours_end', $this->originalConfig['emarsys.working_hours_end']);
        Config::set('emarsys.working_days', $this->originalConfig['emarsys.working_days']);
        Config::set('app.timezone', $this->originalConfig['app.timezone']);
        parent::tearDown();
    }

    public function testCalculateDueDateSameDay()
    {
        $submitDateTime = new SubmitDateTime('2023-05-15 10:00:00'); // Monday
        $turnaroundTime = new TurnaroundTime('2:00'); // 2 hours

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->assertEquals('2023-05-15T12:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateNextDay()
    {
        $submitDateTime = new SubmitDateTime('2023-05-15 16:00:00'); // Monday
        $turnaroundTime = new TurnaroundTime('4:00'); // 4 hours

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->assertEquals('2023-05-16T12:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateOverWeekend()
    {
        $submitDateTime = new SubmitDateTime('2023-05-19 15:00:00'); // Friday
        $turnaroundTime = new TurnaroundTime('16:00'); // 16 hours

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->assertEquals('2023-05-23T15:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateLongTurnaroundTime()
    {
        $submitDateTime = new SubmitDateTime('2023-05-15 09:00:00'); // Monday
        $turnaroundTime = new TurnaroundTime('100:00'); // 100 hours

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->assertEquals('2023-05-31T13:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateStartOfWorkingHours()
    {
        $submitDateTime = new SubmitDateTime('2023-05-15 09:00:00'); // Monday
        $turnaroundTime = new TurnaroundTime('1:00'); // 1 hour

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->assertEquals('2023-05-15T10:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateEndOfWorkingHours()
    {
        $submitDateTime = new SubmitDateTime('2023-05-15 16:59:00'); // Monday
        $turnaroundTime = new TurnaroundTime('0:01'); // 1 minute

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->assertEquals('2023-05-16T09:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateWithDifferentWorkingHours()
    {
        Config::set('emarsys.working_hours_start', '08:00');
        Config::set('emarsys.working_hours_end', '16:00');
        $this->service = new DueDateCalculatorService();

        $submitDateTime = new SubmitDateTime('2023-05-15 15:00:00'); // Monday
        $turnaroundTime = new TurnaroundTime('3:00'); // 3 hours

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->assertEquals('2023-05-16T10:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateWithDifferentWorkingDays()
    {
        Config::set('emarsys.working_days', [1, 2, 3, 4, 5, 6]); // Monday to Saturday
        $this->service = new DueDateCalculatorService();

        $submitDateTime = new SubmitDateTime('2023-05-19 16:00:00'); // Friday
        $turnaroundTime = new TurnaroundTime('10:00'); // 10 hours

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->assertEquals('2023-05-22T10:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateWithDifferentTimezone()
    {
        Config::set('app.timezone', 'America/New_York'); // UTC -4
        $this->service = new DueDateCalculatorService();

        $submitDateTime = new SubmitDateTime('2023-05-15 16:00:00'); // 20:00 UTC
        $turnaroundTime = new TurnaroundTime('4:00'); // 4 hours
        // next noon is the due date

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        // next noon in New York is 16:00 UTC
        $this->assertEquals('2023-05-16T16:00:00Z', (string)$dueDate);
    }

    public function testCalculateDueDateOverDaylightSavingTransition()
    {
        // 2023 spring DST transition is 2023-03-12 02:00:00 UTC
        // America timezone: UTC -5 before DST, UTC -4 after DST
        Config::set('app.timezone', 'America/New_York'); // UTC -4
        $this->service = new DueDateCalculatorService();

        $submitDateTime = new SubmitDateTime('2023-03-10 10:00:00'); // friday
        $turnaroundTime = new TurnaroundTime('8:00'); // 8 hours
        // results monday 10am NY time (after dst)

        $dueDate = $this->service->calculateDueDate($submitDateTime, $turnaroundTime);

        // without DST it would be 2023-03-13T15:00:00Z
        // with DST it must be 2023-03-13T15:00:00Z
        $this->assertEquals('2023-03-13T14:00:00Z', (string)$dueDate);
    }
}
