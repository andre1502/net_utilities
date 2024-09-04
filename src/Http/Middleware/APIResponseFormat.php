<?php

namespace Andre1502\NetUtilities\Http\Middleware;

use Andre1502\NetUtilities\Traits\Config;
use Closure;
use Illuminate\Http\JsonResponse;

class APIResponseFormat
{
  use Config;

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    $response = $next($request);

    if (!isset($response->exception) && $response instanceof JsonResponse) {
      $all = (object) [];

      $original = json_decode(json_encode($response->getData()), false);

      $remarkCode = "SUCCESS";

      $successResult = [
        "httpStatus" => $response->status(),
        "code" => $remarkCode,
        "message" => __("{$this->configName}::remark.{$remarkCode}"),
      ];

      if (!isset($original->result)) {
        $all->result = (object) [];
        foreach ($successResult as $key => $value) {
          if (!isset($original->result->{$key})) {
            $all->result->{$key} = $value;
          }
        }
      } else {
        $all->result = $original->result;
        unset($original->result);
      }

      $all->data = $original->responseData ?? $original;

      $response->setData($all);
    }

    // Perform action

    return $response;
  }
}
