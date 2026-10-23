<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('logs:clear', function () {
  // Clear log files older than 5 days using pure PHP without shelling out to exec/system
  $days = 5;
  $cutoff = Carbon::now()->subDays($days)->getTimestamp();
  $targetDirs = array_filter([
    storage_path('logs'),
    base_path('logs'),
  ], 'is_dir');

  $deletedCount = 0;

  foreach ($targetDirs as $dir) {
    if (!File::isDirectory($dir)) {
      continue;
    }

    $files = File::files($dir);
    foreach ($files as $file) {
      $filename = $file->getFilename();
      if ((str_ends_with($filename, '.log') || str_contains($filename, '.log.')) && $file->getMTime() < $cutoff) {
        File::delete($file->getRealPath());
        $deletedCount++;
      }
    }
  }

  // @phpstan-ignore variable.undefined
  $this->comment(sprintf('Logs have been cleared! (%d file(s) deleted)', $deletedCount));
})->describe('Clear log files older than 5 days safely without shell execution');
