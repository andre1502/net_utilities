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
    {--o|overwrite : Overwrite an existing key.}
    {--f|force : Overwrite an existing key (alias for overwrite).}";

  protected $aliases = [
    "net-utilities:jwt-key",
  ];

  protected $description = "Generate jwt key";

  public function handle(): int
  {
    $jwtAuth = $this->getJwtAuth();
    $jwtSecretKey = "NET_UTILITIES_JWT_SECRET";
    $jwtKey = Str::random(64);
    $envPath = $this->envPath();

    if ($this->option("show")) {
      $current = $jwtAuth["default"]["secret"] ?? "";
      $this->info("Current {$jwtSecretKey}=[{$current}].");

      return Command::SUCCESS;
    }

    if (file_exists($envPath) === false) {
      $this->error('env file not found.');

      return Command::FAILURE;
    }

    $envContent = (string) file_get_contents($envPath);

    if (!Str::contains($envContent, $jwtSecretKey)) {
      file_put_contents($envPath, PHP_EOL . "{$jwtSecretKey}={$jwtKey}" . PHP_EOL, FILE_APPEND);
      $this->info("JWT key generated: {$jwtSecretKey}=[{$jwtKey}].");

      return Command::SUCCESS;
    }

    $match = null;
    if (preg_match("/^{$jwtSecretKey}=(.*)$/m", $envContent, $match)) {
      $currentValue = trim($match[1]);

      if (empty($currentValue)) {
        $this->updateJwtSecret($envPath, $jwtSecretKey, "", $jwtKey, $envContent);

        return Command::SUCCESS;
      }
    }

    $shouldOverwrite = $this->option("overwrite") || $this->option("force");

    if (!$shouldOverwrite) {
      $this->comment("No key changed, to overwrite please run command with [-o | --overwrite | --force] argument.");

      return Command::SUCCESS;
    }

    $currentSecret = $jwtAuth["default"]["secret"] ?? "";
    $this->updateJwtSecret($envPath, $jwtSecretKey, $currentSecret, $jwtKey, $envContent);

    return Command::SUCCESS;
  }

  private function envPath(): string
  {
    if (method_exists($this->laravel, 'environmentFilePath')) {
      return (string) $this->laravel->environmentFilePath();
    }

    return (string) $this->laravel->basePath('.env');
  }

  private function updateJwtSecret(string $envPath, string $jwtSecretKey, string $currentKey, string $jwtKey, string $envContent): void
  {
    $replacement = (!empty($currentKey))
      ? Str::replace("{$jwtSecretKey}={$currentKey}", "{$jwtSecretKey}={$jwtKey}", $envContent)
      : preg_replace("/^{$jwtSecretKey}=.*$/m", "{$jwtSecretKey}={$jwtKey}", $envContent);

    file_put_contents($envPath, $replacement);

    $this->info("JWT key generated: {$jwtSecretKey}=[{$jwtKey}].");
  }
}
