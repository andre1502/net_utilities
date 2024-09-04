<?php

namespace Andre1502\NetUtilities\Traits\Commands;

trait PrependsEnvironment
{
  protected function getPrependsEnvironment($style = "debug")
  {
    return sprintf("%s.%s: ", config("app.env"), $style);
  }
}
