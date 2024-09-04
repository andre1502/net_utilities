<?php

namespace Andre1502\NetUtilities\Http\Middleware;

use Andre1502\NetUtilities\Traits\Utils;
use Closure;
use Illuminate\Http\Request;

class DashboardBasicAuth
{
  use Utils;

  public function __construct()
  {
  }

  /**
   * Handle an incoming request.
   *
   * @param  Request  $request
   * @param  Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    $clientIP = $this->getClientIp();
    $authUsername = config("{$this->configName}.basic_auth.username");
    $authPassword = config("{$this->configName}.basic_auth.password");
    $allowedIps = config("{$this->configName}.basic_auth.allowed_ip");

    if (!empty($allowedIps)) {
      $allowedIps = explode(",", $allowedIps);
    }

    if (!in_array($clientIP, $allowedIps)) {
      return response()->make("Unauthorized.", 403, ["WWW-Authenticate" => "Basic"]);
    }

    if ($request->header("PHP_AUTH_USER", null) && $request->header("PHP_AUTH_PW", null)) {
      $username = $request->header("PHP_AUTH_USER");
      $password = $request->header("PHP_AUTH_PW");

      if (($username === $authUsername) && ($password === $authPassword)) {
        return $next($request);
      }
    }

    return response()->make("Invalid credentials.", 401, ["WWW-Authenticate" => "Basic"]);
  }
}
