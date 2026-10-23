<?php

use Andre1502\NetUtilities\Security\PHP2ShellProtection;
use Andre1502\NetUtilities\Traits\HttpRequest;
use Andre1502\NetUtilities\Traits\JWT;
use Andre1502\NetUtilities\Traits\Utils;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

use Andre1502\NetUtilities\NetUtilitiesProvider;

class AppHelper
{
    use JWT;
    use Utils;
    use HttpRequest;
}

test('package service provider is auto-discovered and registered by Laravel', function () {
    // 1. Verify NetUtilitiesProvider was auto-discovered by Laravel PackageManifest
    expect(app()->providerIsLoaded(NetUtilitiesProvider::class))->toBeTrue();

    // 2. Verify package config was automatically merged into Laravel config repository
    expect(config('net-utilities'))->toBeArray()
        ->and(config('net-utilities.log_channel'))->toBe('RequestAPI');

    // 3. Verify package configured custom logging channel inside Laravel
    expect(config('logging.channels.RequestAPI'))->toBeArray()
        ->and(config('logging.channels.RequestAPI.driver'))->toBe('daily');
});

test('package routes are auto-registered and respond correctly', function () {
    $response = $this->get('/health-check');
    $response->assertStatus(200);
    expect($response->getContent())->toContain('OK');
});

test('package JWT trait works seamlessly inside standalone application', function () {
    $helper = new AppHelper();
    $token = $helper->issueToken(777, ['role' => 'editor', 'tenant' => 'primary']);

    expect($token)->toBeString();

    $parsed = $helper->validateToken($token);
    expect($helper->getClaimByKey($parsed, 'uid'))->toBe(777)
        ->and($helper->getClaimByKey($parsed, 'role'))->toBe('editor');
});

test('package Utils trait functions correctly in standalone application', function () {
    $helper = new AppHelper();

    expect($helper->convert(2048))->toBe('2 kb')
        ->and($helper->floatdiv(50, 5))->toBe(10.0)
        ->and($helper->roundUp(4.211, 2))->toBe(4.22)
        ->and($helper->getTransId('ORD'))->toStartWith('ORD');
});

test('package HttpRequest trait works with Laravel HTTP client in standalone application', function () {
    Http::fake([
        'https://external.service/api/*' => Http::response(['message' => 'pong'], 200, ['Content-Type' => 'application/json']),
    ]);

    $helper = new AppHelper();
    $result = $helper->requestAPI('https://external.service/api/ping', null, null, null, null, 'GET');

    expect($result['status'])->toBeTrue()
        ->and($result['status_code'])->toBe(200)
        ->and($result['response'])->toBe(['message' => 'pong']);
});

test('package middleware aliases are registered in the standalone router', function () {
    Route::get('/test-auth-token', function () {
        return response()->json(['secret' => 'granted']);
    })->middleware('apiCheckAuthToken');

    // Missing token should fail
    $failResponse = $this->getJson('/test-auth-token');
    $failResponse->assertStatus(401);

    // Valid internal token should pass
    $passResponse = $this->withHeader('X-Internal-Token', 'standalone-secret-token')
        ->getJson('/test-auth-token');
    $passResponse->assertStatus(200);
});

test('artisan security-check runs inside standalone project', function () {
    $exitCode = Artisan::call('net-utilities:security-check');
    $output = Artisan::output();

    expect($output)->toContain('=== Net Utilities Security Audit ===')
        ->and($output)->toContain('PHP Runtime Version')
        ->and($output)->toContain('Zero shell/exec calls in package');
});

test('artisan logs:clear safely cleans log files in standalone project without exec', function () {
    $logDir = storage_path('logs');
    if (!File::isDirectory($logDir)) {
        File::makeDirectory($logDir, 0755, true);
    }

    $oldLog = $logDir . '/app-old.log';
    file_put_contents($oldLog, 'old logs');
    touch($oldLog, time() - (10 * 86400));

    $freshLog = $logDir . '/app-fresh.log';
    file_put_contents($freshLog, 'new logs');
    touch($freshLog, time());

    $exitCode = Artisan::call('logs:clear');
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and(file_exists($oldLog))->toBeFalse()
        ->and(file_exists($freshLog))->toBeTrue();

    File::delete($freshLog);
});

test('artisan net-utilities:publish publishes package assets successfully', function () {
    $exitCode = Artisan::call('net-utilities:publish');
    expect($exitCode)->toBe(0);
});
