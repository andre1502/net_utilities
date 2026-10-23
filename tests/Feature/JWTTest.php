<?php

use Andre1502\NetUtilities\Exceptions\APIErrorException;
use Andre1502\NetUtilities\Traits\JWT;

class JWTDummy
{
    use JWT;
}

beforeEach(function () {
    $this->jwt = new JWTDummy();
});

test('issueToken generates valid JWT and parseToken extracts claims', function () {
    $tokenString = $this->jwt->issueToken(12345, ['role' => 'admin', 'tenant' => 'corp']);

    expect($tokenString)->toBeString()
        ->and(substr_count($tokenString, '.'))->toBe(2);

    $parsed = $this->jwt->parseToken($tokenString);
    expect($this->jwt->getClaimByKey($parsed, 'uid'))->toBe(12345)
        ->and($this->jwt->getClaimByKey($parsed, 'sub'))->toBe('12345')
        ->and($this->jwt->getClaimByKey($parsed, 'role'))->toBe('admin');
});

test('validateToken successfully verifies valid token', function () {
    $tokenString = $this->jwt->issueToken(99, ['scope' => 'read']);
    $validated = $this->jwt->validateToken($tokenString);

    expect($validated->claims()->get('uid'))->toBe(99);
});

test('validateToken throws APIErrorException when signature is invalid', function () {
    $tokenString = $this->jwt->issueToken(99, ['scope' => 'read']);

    expect(fn () => $this->jwt->validateToken($tokenString, 'different-wrong-secret-key-at-least-32-chars!'))
        ->toThrow(APIErrorException::class);
});
