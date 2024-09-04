<?php

namespace Andre1502\NetUtilities\Traits\Commands;

use Andre1502\NetUtilities\Traits\Config;

trait PrependsTimestamp
{
  use Config;

  protected function getPrependTimestamp()
  {
    $dateTimeFormat = $this->getDatetimeFormat();

    return date(property_exists($this, "outputTimestampFormat") ?
        $this->outputTimestampFormat : sprintf("[%s]", $dateTimeFormat)) . " ";
  }
}
