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
   * @return bool|null
   */
  public function report()
  {
    $exTrace = $this->getTrace();
    $reportMessage = sprintf("[%s->%s:%s] - Message: %s.", $exTrace[0]["class"], $exTrace[0]["function"], $this->getLine(), print_r($this->getMessage(), true));

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
