<?php

namespace Andre1502\NetUtilities\Console\Commands;

use Andre1502\NetUtilities\Traits\Config;
use Illuminate\Console\Command;

class Publish extends Command
{
  use Config;

  protected $signature = "net-utilities:publish";
  protected $description = "Publish Net Utilities assets";

  public function handle()
  {
    $this->call("vendor:publish", [
      "--tag" => "{$this->configName}-assets",
      "--force" => true,
    ]);
  }
}
