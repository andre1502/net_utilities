<?php

use Andre1502\NetUtilities\Exceptions\FailedAPIConnectionException;
use Andre1502\NetUtilities\Traits\HttpRequest;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Request;

class HttpRequestDummy
{
    use HttpRequest;
}

beforeEach(function () {
    $this->http = new HttpRequestDummy();
});

test('setTimeoutAndRetry configures timeout, retry, and retryIntervalMs properly', function () {
    $this->http->setTimeoutAndRetry(45, 5, 250);

    // Verify reflection properties
    $reflection = new ReflectionClass($this->http);
    $timeoutProp = $reflection->getProperty('timeout');
    $retryProp = $reflection->getProperty('retry');
    $retryIntervalProp = $reflection->getProperty('retryIntervalMs');

    expect($timeoutProp->getValue($this->http))->toBe(45)
        ->and($retryProp->getValue($this->http))->toBe(5)
        ->and($retryIntervalProp->getValue($this->http))->toBe(250);
});

test('setCurrencyRequestUrl swaps :currency token', function () {
    $url = $this->http->setCurrencyRequestUrl('https://api.example.com/rates/:currency/daily', 'USD');
    expect($url)->toBe('https://api.example.com/rates/USD/daily');
});

test('requestAPI performs HTTP request and parses JSON response on 200', function () {
    Http::fake([
        'https://api.example.com/*' => Http::response(['status' => 'ok', 'count' => 10], 200, ['Content-Type' => 'application/json']),
    ]);

    $result = $this->http->requestAPI(
        'https://api.example.com/data',
        'sample-token',
        ['X-Custom' => 'header'],
        ['param' => 'test'],
        ['query' => 'value'],
        Request::METHOD_POST
    );

    expect($result['status'])->toBeTrue()
        ->and($result['status_code'])->toBe(200)
        ->and($result['response'])->toBe(['status' => 'ok', 'count' => 10])
        ->and($result['exception'])->toBeNull();
});

test('requestAPI handles connection failure gracefully', function () {
    Http::fake([
        'https://api.unreachable.local/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection timed out'),
    ]);

    expect(fn () => $this->http->requestAPI(
        'https://api.unreachable.local/test',
        null,
        null,
        null,
        null,
        Request::METHOD_GET
    ))->toThrow(FailedAPIConnectionException::class);
});
