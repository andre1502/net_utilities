<?php

namespace Andre1502\NetUtilities\Http\Middleware;

use Andre1502\NetUtilities\Exceptions\APIErrorException;
use Andre1502\NetUtilities\Traits\Config;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class APICheckAuthToken
{
  use Config;

  /**
   * Construct
   */
  public function __construct()
  {
  }

  /**
   * Handle an incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    try {
      $internalApiAuth = config("{$this->configName}.internal_api_auth");
      $internalApiToken = $internalApiAuth["token"];
      $authToken = $request->header($internalApiAuth["header"]);

      if ($internalApiToken === $authToken) {
        return $next($request);
      } else {
        throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.UNAUTHORIZED");
      }
    } catch (Exception $ex) {
      report($ex);
      throw new APIErrorException(Response::HTTP_UNAUTHORIZED, "{$this->configName}::error.UNAUTHORIZED");
    }
  }
}
