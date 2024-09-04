<?php

namespace Andre1502\NetUtilities\Console\Commands;

use Andre1502\NetUtilities\Traits\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateJWTSecret extends Command
{
  use Config;

  protected $signature = "net-utilities:jwtkey
    {--s|show : Display the saved key.}
    {--o|overwrite : Overwrite an existing key.}";
  protected $description = "Generate jwt key";

  public function handle()
  {
    $jwtAuth = $this->getJwtAuth();
    $jwtSecretKey = "NET_UTILITIES_JWT_SECRET";
    $jwtKey = Str::random(64);
    $envPath = $this->envPath();

    if ($this->option("show")) {
      $this->info("Current {$jwtSecretKey}=[{$jwtAuth["default"]["secret"]}].");

      return;
    }

    if (file_exists($envPath) === false) {
      $this->error('env file not found.');

      return;
    }

    $envContent = file_get_contents($envPath);

    if (!Str::contains($envContent, $jwtSecretKey)) {
      file_put_contents($jwtKey, PHP_EOL . "{$jwtSecretKey}={$jwtKey}" . PHP_EOL, FILE_APPEND);
    } else {
      $match = null;

      if (preg_match("/{$jwtSecretKey}=.*$/m", $envContent, $match)) {
        $tmp = Str::of($match[0])->explode("=");

        if (empty($tmp[1])) {
          $this->updateJwtSecret($envPath, $jwtSecretKey, "", $jwtKey, $envContent);

          return;
        }
      }

      if (!$this->option("overwrite")) {
        $this->comment("No key changed, to overwrite please run command with [-o | --overwrite] argument.");

        return;
      }

      $this->updateJwtSecret($envPath, $jwtSecretKey, $jwtAuth["default"]["secret"], $jwtKey, $envContent);
    }
  }

  private function envPath()
  {
    if (method_exists($this->laravel, 'environmentFilePath')) {
      return $this->laravel->environmentFilePath();
    }

    return $this->laravel->basePath('.env');
  }

  private function updateJwtSecret(string $envPath, string $jwtSecretKey, string $currentKey, string $jwtKey, string $envContent)
  {
    file_put_contents($envPath, Str::replace(
      "{$jwtSecretKey}={$currentKey}",
      "{$jwtSecretKey}={$jwtKey}", $envContent
    ));

    $this->info("JWT key generated: {$jwtSecretKey}=[{$jwtKey}].");
  }
}
