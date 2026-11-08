<?php

use Illuminate\Support\Facades\Schedule;

// F4: close requests whose deadline has passed. Locally, run `php artisan schedule:work`
// to keep the scheduler going, or run `php artisan requests:expire` by hand.
Schedule::command('requests:expire')->hourly();
