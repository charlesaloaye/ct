<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('articles:publish-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('site:check-links')->weeklyOn(1, '06:00')->withoutOverlapping();
