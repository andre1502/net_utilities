<?php

use Andre1502\NetUtilities\Exceptions\APIErrorException;
use Andre1502\NetUtilities\Http\Middleware\APICheckAuthToken;
use Andre1502\NetUtilities\Http\Middleware\APIResponseFormat;
use Andre1502\NetUtilities\Http\Middleware\DashboardBasicAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

test('DashboardBasicAuth permits allowed IP and valid basic auth', function () {
    $middleware = new DashboardBasicAuth();

    $request = Request::create('/dashboard', 'GET');
    $request->server->set('REMOTE_ADDR', '127.0.0.1');
    $request->headers->set('PHP_AUTH_USER', 'admin');
    $request->headers->set('PHP_AUTH_PW', 'secret123');
    app()->instance('request', $request);

    $response = $middleware->handle($request, function ($req) {
        return response('Dashboard content');
    });

    expect($response->getContent())->toBe('Dashboard content');
});

test('DashboardBasicAuth rejects unauthorized IP', function () {
    $middleware = new DashboardBasicAuth();

    $request = Request::create('/dashboard', 'GET');
    $request->server->set('REMOTE_ADDR', '198.51.100.1');
    app()->instance('request', $request);

    $response = $middleware->handle($request, function ($req) {
        return response('Should not reach');
    });

    expect($response->getStatusCode())->toBe(403);
});

test('APICheckAuthToken permits matching internal token', function () {
    $middleware = new APICheckAuthToken();

    $request = Request::create('/internal/api', 'GET');
    $request->headers->set('X-Internal-Token', 'internal-secret-token');

    $response = $middleware->handle($request, function ($req) {
        return response('Authorized API');
    });

    expect($response->getContent())->toBe('Authorized API');
});

test('APICheckAuthToken throws APIErrorException when token is missing or mismatched', function () {
    $middleware = new APICheckAuthToken();

    $request = Request::create('/internal/api', 'GET');
    $request->headers->set('X-Internal-Token', 'invalid-token');

    expect(fn () => $middleware->handle($request, function ($req) {}))
        ->toThrow(APIErrorException::class);
});

test('APIResponseFormat formats JSON responses into standardized envelope', function () {
    $middleware = new APIResponseFormat();

    $request = Request::create('/api/items', 'GET');

    $response = $middleware->handle($request, function ($req) {
        return response()->json(['item_id' => 42, 'name' => 'Widget']);
    });

    expect($response)->toBeInstanceOf(JsonResponse::class);

    $data = $response->getData(true);
    expect($data)->toHaveKey('result')
        ->and($data)->toHaveKey('data')
        ->and($data['result']['httpStatus'])->toBe(200)
        ->and($data['result']['code'])->toBe('SUCCESS')
        ->and($data['data'])->toBe(['item_id' => 42, 'name' => 'Widget']);
});
