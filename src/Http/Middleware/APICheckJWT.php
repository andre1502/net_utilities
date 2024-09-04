<?php

namespace Andre1502\NetUtilities\Http\Middleware;

use Andre1502\NetUtilities\Exceptions\APIErrorException;
use Andre1502\NetUtilities\Traits\HttpRequest;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class APICheckJWT
{
  use HttpRequest;

  /**
   * Handle an incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(\Illuminate\Http\Request $request, Closure $next)
  {
    $locale = config("{$this->configName}.locale");
    $checkJwtUrl = config("{$this->configName}.apis.check_jwt");

    $lang = $request->lang ?? $locale;
    $token = $request->header("Authorization");

    if (empty($token)) {
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.INVALID_TOKEN");
    }

    try {
      $result = $this->requestAPI($checkJwtUrl, $token, null, null, ["lang" => $lang], Request::METHOD_GET);
      $responseData = $result["response"];
      $user = [];

      if ((!$result["status"]) || (!empty($result["exception"]))) {
        if ($result["status_code"] === Response::HTTP_UNAUTHORIZED) {
          throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.INVALID_TOKEN");
        }

        throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.FAILED_CHECK_JWT");
      } elseif ((!empty($responseData)) && (!empty($responseData["result"])) && ($responseData["result"]["code"] === 0) && (!empty($responseData["data"]))) {
        $user = $responseData["data"];
      }

      if (empty($user)) {
        throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.EMPTY_JWT_USER");
      }

      $request->merge([
        "lang" => $lang,
        "userInfo" => $user,
      ]);
    } catch (Exception $ex) {
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.FAILED_CHECK_JWT");
    }

    return $next($request);
  }
}
