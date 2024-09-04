<?php

namespace Andre1502\NetUtilities\Exceptions;

use Andre1502\NetUtilities\Traits\Config;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class APIErrorException extends Exception
{
  use Config;

  protected string $logChannel;
  protected int $httpStatus;
  protected array $args = [];

  public function __construct(int $httpStatus, ?string $errorCode, array $args = [], array $params = [], ?string $logChannel = null)
  {
    $locale = App::currentLocale();
    $translationKey = $errorCode;

    if (($httpStatus != Response::HTTP_OK) && (empty($errorCode))) {
      $errorCode = "error.FAILED";
      $translationKey = "{$this->configName}::{$errorCode}";
    }

    if (!Lang::has($errorCode, $locale)) {
      $this->logReport("Receive unknown error code: {$errorCode}", []);

      $errorCode = "error.UNKNOWN";
      $translationKey = "{$this->configName}::{$errorCode}";
    }

    if (!empty($logChannel)) {
      $this->logChannel = $logChannel;
    }

    $this->httpStatus = $httpStatus;
    $this->code = Str::replace("{$this->configName}::", "", $errorCode);
    $this->args = $args;
    $this->message = (!empty($params)) ? __($translationKey, $params) : __($translationKey);
  }

  /**
   * Get the exception"s context information.
   *
   * @return array
   */
  public function context()
  {
    return $this->args;
  }

  /**
   * Report the exception.
   *
   * @return bool|null
   */
  public function report()
  {
    $exTrace = $this->getTrace();
    $reportMessage = sprintf("[%s->%s:%s][%s] - Message: %s.", $exTrace[0]["class"], $exTrace[0]["function"], $this->getLine(), $this->getCode(),
      print_r($this->getMessage(), true));

    $this->logReport($reportMessage, $this->context());
  }

  private function logReport(string $message, array $context) : void
  {
    if (empty($this->logChannel)) {
      Log::error($message, $context);
    } else {
      Log::channel($this->logChannel)->error($message, $context);
    }
  }

  /**
   * Render the exception into an HTTP response.
   *
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
  public function render(Request $request)
  {
    return response()->json([
      "result" => [
        "httpStatus" => $this->httpStatus,
        "code" => $this->getCode(),
        "message" => $this->getMessage(),
      ],
      "data" => (object) [],
    ], $this->httpStatus);
  }
}
