<?php

namespace Andre1502\NetUtilities\Traits\Commands;

trait PrependsOutput
{
  public function line($message, $style = "debug", $verbosity = null)
  {
    $context = [];
    $className = get_class($this);
    $strNeedle = "App\Console\Commands";
    $lenNeedle = strlen($strNeedle) + 1;
    $posIdx = strpos($className, $strNeedle);
    if ((property_exists($this, "signature")) && ($posIdx !== false)) {
      $commandTag = substr($className, ($posIdx + $lenNeedle));

      $context["command_tag"] = $commandTag;
    }

    parent::line($this->prepend($message, $style), $style, $verbosity);
  }

  protected function prepend($message, $style = "debug")
  {
    $prependMessage = "";

    if (method_exists($this, "getPrependTimestamp")) {
      $prependMessage = $this->getPrependTimestamp();
    }

    if (method_exists($this, "getPrependsEnvironment")) {
      $prependMessage .= $this->getPrependsEnvironment($style);
    }

    return $prependMessage . $message;
  }
}
