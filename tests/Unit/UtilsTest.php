<?php

use Andre1502\NetUtilities\Traits\Utils;

class UtilsDummy
{
    use Utils;
}

beforeEach(function () {
    $this->utils = new UtilsDummy();
});

test('frand generates float within specified bounds', function () {
    $val = $this->utils->frand(1, 10, 2);
    expect($val)->toBeGreaterThanOrEqual(1.0)
        ->and($val)->toBeLessThanOrEqual(10.0);
});

test('roundUp and roundDown work as expected', function () {
    expect($this->utils->roundUp(3.14159, 2))->toBe(3.15)
        ->and($this->utils->roundDown(3.14159, 2))->toBe(3.14);
});

test('floatdiv handles normal division and division by zero', function () {
    expect($this->utils->floatdiv(10, 2))->toBe(5.0)
        ->and($this->utils->floatdiv(10, 0))->toBe(0.0)
        ->and($this->utils->floatdiv(0, 5))->toBe(0.0);
});

test('jsonValidate returns decoded data for valid json and string on invalid', function () {
    $validJson = '{"key":"value","number":123}';
    $validResult = $this->utils->jsonValidate($validJson);

    expect($validResult['error'])->toBeEmpty()
        ->and($validResult['data'])->toBe(['key' => 'value', 'number' => 123]);

    $invalidJson = '{"key": incomplete';
    $invalidResult = $this->utils->jsonValidate($invalidJson);

    expect($invalidResult['error'])->not()->toBeEmpty()
        ->and($invalidResult['data'])->toBe($invalidJson);
});

test('convert formats bytes into human readable format', function () {
    expect($this->utils->convert(0))->toBe('0 b')
        ->and($this->utils->convert(1024))->toBe('1 kb')
        ->and($this->utils->convert(1048576))->toBe('1 mb')
        ->and($this->utils->convert(1073741824))->toBe('1 gb');
});

test('getTransId produces string with timestamp and random component', function () {
    $transId = $this->utils->getTransId('TX', 8);
    expect($transId)->toStartWith('TX')
        ->and(strlen($transId))->toBeGreaterThan(12);
});

test('removePaginateUrlPath strips unwanted pagination keys', function () {
    $input = [
        'path' => 'https://example.com/api',
        'first_page_url' => 'https://example.com/api?page=1',
        'last_page_url' => 'https://example.com/api?page=5',
        'current_page' => 1,
        'data' => [1, 2, 3],
    ];

    $result = $this->utils->removePaginateUrlPath($input);

    expect($result)->not()->toHaveKey('path')
        ->and($result)->not()->toHaveKey('first_page_url')
        ->and($result)->toHaveKey('current_page')
        ->and($result)->toHaveKey('data');
});

test('updateFileUrl prefixes server domain properly', function () {
    $list = [
        ['id' => 1, 'avatar' => '/uploads/avatar.jpg', 'name' => 'Alice'],
    ];

    $updated = $this->utils->updateFileUrl($list, ['avatar'], 'https://cdn.example.com');

    expect($updated[0]['avatar'])->toBe('https://cdn.example.com/uploads/avatar.jpg')
        ->and($updated[0]['name'])->toBe('Alice');
});
