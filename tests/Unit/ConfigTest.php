<?php

use Andre1502\NetUtilities\Traits\Config;

class ConfigDummy
{
    use Config;
}

beforeEach(function () {
    $this->config = new ConfigDummy();
});

test('getLogChannel returns configured or default channel', function () {
    expect($this->config->getLogChannel())->toBe('RequestAPI');
});

test('getTimezone returns UTC by default', function () {
    expect($this->config->getTimezone())->toBe('UTC');
});

test('getDatetimeFormat returns default format', function () {
    expect($this->config->getDatetimeFormat())->toBe('Y-m-d H:i:s');
});

test('getJwtAuth returns array configuration', function () {
    expect($this->config->getJwtAuth())->toBeArray()
        ->and($this->config->getJwtAuth())->toHaveKey('default');
});

test('getRedisKeys returns redis key definitions', function () {
    expect($this->config->getRedisKeys())->toBeArray();
});
