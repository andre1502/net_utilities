<?php

namespace Andre1502\NetUtilities\Tests;

use Andre1502\NetUtilities\NetUtilitiesProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            NetUtilitiesProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.name', 'NetUtilitiesTest');
        $app['config']->set('app.url', 'http://localhost');
        $app['config']->set('app.env', 'testing');
        $app['config']->set('net-utilities.jwt_auth.default.secret', 'test-secret-key-that-is-at-least-32-bytes-long!!');
        $app['config']->set('net-utilities.jwt_auth.default.ttl', 60);
        $app['config']->set('net-utilities.jwt_auth.check_jwt.secret', 'test-secret-key-that-is-at-least-32-bytes-long!!');
        $app['config']->set('net-utilities.basic_auth.username', 'admin');
        $app['config']->set('net-utilities.basic_auth.password', 'secret123');
        $app['config']->set('net-utilities.basic_auth.allowed_ip', '127.0.0.1');
        $app['config']->set('net-utilities.internal_api_auth.header', 'X-Internal-Token');
        $app['config']->set('net-utilities.internal_api_auth.token', 'internal-secret-token');
    }
}
