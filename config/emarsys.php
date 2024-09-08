<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Working Hours
    |--------------------------------------------------------------------------
    |
    | Define the start and end of working hours.
    | Format: HH:MM in 24-hour notation.
    |
    */
    'working_hours_start' => env('WORKING_HOURS_START', '09:00'),
    'working_hours_end' => env('WORKING_HOURS_END', '17:00'),

    /*
    |--------------------------------------------------------------------------
    | Working Days
    |--------------------------------------------------------------------------
    |
    | Define the working days of the week.
    | 1 (for Monday) through 7 (for Sunday).
    |
    */
    // make array of numbers, from comma separated string, remove empty values
    'working_days' => array_filter(array_map('intval', explode(',', env('WORKING_DAYS', '1,2,3,4,5')))),

];
