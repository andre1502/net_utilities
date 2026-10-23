<?php

namespace Andre1502\NetUtilities\Exceptions;

use Andre1502\NetUtilities\Traits\Config;
use Exception;
use Illuminate\Support\Facades\Log;

class BaseException extends Exception
{
  use Config;

  protected string $logChannel;

  public function __construct(string $message = "", int $code = 0)
  {
    $this->logChannel = $this->getLogChannel();
    $this->message = $message;
    $this->code = $code;
  }

  /**
   * Report the exception.
   *
   * @return void
   */
  public function report(): void
  {
    $exTrace = $this->getTrace();
    $class = $exTrace[0]["class"] ?? 'N/A';
    $function = $exTrace[0]["function"] ?? 'N/A';
    $reportMessage = sprintf("[%s->%s:%s] - Message: %s.", $class, $function, $this->getLine(), print_r($this->getMessage(), true));

    $this->logReport($reportMessage);
  }

  private function logReport(string $message) : void
  {
    if (empty($this->logChannel)) {
      Log::error($message);
    } else {
      Log::channel($this->logChannel)->error($message);
    }
  }
}
