<?php

namespace Andre1502\NetUtilities\Http\Middleware;

use Andre1502\NetUtilities\Exceptions\APIErrorException;
use Andre1502\NetUtilities\Traits\JWT;
use Cache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RedisCheckJWT
{
  use JWT;

  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    $locale = config("{$this->configName}.locale");
    $checkJwtRedisName = $this->getCheckJwtRedisName();
    $checkJwtRedisKey = $this->getRedisKey("check_jwt");
    $checkJwtSecret = $this->getJwtAuth()["check_jwt"]["secret"];
    $lang = $request->lang ?? $locale;
    $bearerToken = $request->bearerToken();

    if (empty($checkJwtRedisName) || empty($checkJwtRedisKey)) {
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.FAILED_CHECK_JWT");
    }

    if (empty($bearerToken)) {
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.INVALID_TOKEN");
    }

    $parsedToken = $this->validateToken($bearerToken, $checkJwtSecret);
    $sub = $parsedToken->claims()->get("sub");
    $userId = $parsedToken->claims()->get("uid");
    $accountId = $parsedToken->claims()->get("aid");
    $userId = (empty($userId)) ? $sub : $userId;

    $cachedToken = Cache::store($checkJwtRedisName)->get(Str::replace("[user]", $userId, $checkJwtRedisKey));

    if ((empty($cachedToken)) || ($bearerToken !== $cachedToken)) {
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.INVALID_TOKEN");
    }

    $request->merge([
      "lang" => $lang,
      "userId" => $userId,
      "accountId" => $accountId,
    ]);

    return $next($request);
  }
}
