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
        $this->line('Welcome to the Due Date Calculator');

        $this->displayParameterExplanation();
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
        $this->line('Working hours start: ' . config('emarsys.working_hours_start'));
        $this->line('Working hours end: ' . config('emarsys.working_hours_end'));
        $this->line('Working days: ' . join(', ', config('emarsys.working_days')));
        $this->line('Timezone: ' . config('app.timezone'));
        $workdayMinutes = $this->calculator->calculateWorkdayMinutes();
        $this->line('Working hours per day: ' . ($workdayMinutes / 60));
    }

    private function displayParameterExplanation()
    {
        $this->line("\nParameter Explanation:");
        $this->line(' » submitDateTime: The date and time when the issue was submitted (format: "YYYY-MM-DD HH:mm")');
        $this->line(' » turnaroundTime: The working hours needed to resolve the issue (format: HH or HH:mm)');
        $this->line(' (mind the quotes if contains spaces)');
        $this->line('');
    }

    private function displayInputs(SubmitDateTime $submitDateTime, TurnaroundTime $turnaroundTime)
    {
        $this->warn("\nInputs:");
        $this->warn(' » Submit datetime: ' . $submitDateTime->getDateTime()->format('Y-m-d H:i (l)'));
        $this->warn(' » Turnaround time: ' . $turnaroundTime . ' (' . $turnaroundTime->getMinutes() . ' minutes)');
    }

    private function displayDueDate($dueDate)
    {
        $this->info("\nDue datetime: " . $dueDate->getDateTime()->format('Y-m-d H:i (l)'));
        $this->info("ISO: " . $dueDate);
    }
}
