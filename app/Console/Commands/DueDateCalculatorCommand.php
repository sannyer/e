<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DueDateCalculatorService;
use App\DataObjects\SubmitDateTime;
use App\DataObjects\TurnaroundTime;

class DueDateCalculatorCommand extends Command
{
    protected $signature = 'calculator {submitDateTime} {turnaroundTime}';
    protected $description = 'Due Date Calculator';

    private $calculator;

    public function __construct(DueDateCalculatorService $calculator)
    {
        parent::__construct();
        $this->calculator = $calculator;
    }

    public function handle()
    {
        $this->info('Welcome to the Due Date Calculator');

        $this->displayConfiguration();

        $submitDateTime = new SubmitDateTime($this->argument('submitDateTime'));
        $turnaroundTime = new TurnaroundTime($this->argument('turnaroundTime'));

        $this->displayInputs($submitDateTime, $turnaroundTime);

        $dueDate = $this->calculator->calculateDueDate($submitDateTime, $turnaroundTime);

        $this->displayDueDate($dueDate);
    }

    // make a method to display the current configuration
    private function displayConfiguration()
    {
        $this->info('Working hours start: ' . config('emarsys.working_hours_start'));
        $this->info('Working hours end: ' . config('emarsys.working_hours_end'));
        $this->info('Working days: ' . join(', ', config('emarsys.working_days')));
        $this->info('Timezone: ' . config('app.timezone'));
        $workdayMinutes = $this->calculator->calculateWorkdayMinutes();
        $this->info('Working hours per day: ' . ($workdayMinutes / 60));
    }

    private function displayInputs(SubmitDateTime $submitDateTime, TurnaroundTime $turnaroundTime)
    {
        $this->info("\nInputs:");
        $this->info(' » Submit datetime: ' . $submitDateTime->getDateTime()->format('Y-m-d H:i (l)'));
        $this->info(' » Turnaround time: ' . $turnaroundTime . ' (' . $turnaroundTime->getMinutes() . ' minutes)');
    }

    private function displayDueDate($dueDate)
    {
        $this->info("\nDue datetime: " . $dueDate->getDateTime()->format('Y-m-d H:i (l)'));
    }
}
