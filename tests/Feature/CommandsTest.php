<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

test('GenerateJWTSecret --show displays current secret', function () {
    $exitCode = Artisan::call('net-utilities:jwtkey', ['--show' => true]);
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('Current NET_UTILITIES_JWT_SECRET=');
});

test('GenerateJWTSecret alias net-utilities:jwt-key works', function () {
    $exitCode = Artisan::call('net-utilities:jwt-key', ['--show' => true]);
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('Current NET_UTILITIES_JWT_SECRET=');
});

test('logs:clear executes safely without shell exec and reports deleted files', function () {
    $logDir = storage_path('logs');
    if (!File::isDirectory($logDir)) {
        File::makeDirectory($logDir, 0755, true);
    }

    $oldLogFile = $logDir . '/test-old.log';
    file_put_contents($oldLogFile, 'old log content');
    // Set mtime to 10 days ago
    touch($oldLogFile, time() - (10 * 86400));

    $freshLogFile = $logDir . '/test-fresh.log';
    file_put_contents($freshLogFile, 'fresh log content');
    touch($freshLogFile, time());

    $exitCode = Artisan::call('logs:clear');
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('Logs have been cleared!')
        ->and(file_exists($oldLogFile))->toBeFalse()
        ->and(file_exists($freshLogFile))->toBeTrue();

    // Cleanup fresh log file
    File::delete($freshLogFile);
});
