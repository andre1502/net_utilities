<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('logs:clear', function () {
  exec(sprintf("find %s -name '*.log' -type f -atime +5 -delete", storage_path('logs')));
  exec(sprintf("find %s -name '*.log' -type f -atime +5 -delete", base_path('logs')));
  exec(sprintf("find %s -name '*.log.*' -type f -atime +5 -delete", storage_path('logs')));
  exec(sprintf("find %s -name '*.log.*' -type f -atime +5 -delete", base_path()));

  $this->comment('Logs have been cleared!');
})->describe('Clear log files');
