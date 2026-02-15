<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sessions:expire')->everyMinute();
Schedule::command('model:prune')->hourly();
