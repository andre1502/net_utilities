<?php

namespace Andre1502\NetUtilities;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Andre1502\NetUtilities\Console\Commands\GenerateJWTSecret;
use Andre1502\NetUtilities\Console\Commands\Publish;
use Andre1502\NetUtilities\Http\Middleware\APICheckAuthToken;
use Andre1502\NetUtilities\Http\Middleware\APICheckJWT;
use Andre1502\NetUtilities\Http\Middleware\APIResponseFormat;
use Andre1502\NetUtilities\Http\Middleware\DashboardBasicAuth;
use Andre1502\NetUtilities\Http\Middleware\RedisCheckJWT;
use Andre1502\NetUtilities\Traits\Config;

class NetUtilitiesProvider extends ServiceProvider
{
  use Config;

  public static function basePath(string $path) : string
  {
    return __DIR__ . "/.." . $path;
  }

  /**
   * Register services.
   *
   * @return void
   */
  public function register()
  {
    $this->mergeConfigFrom(self::basePath("/config/{$this->configName}.php"), $this->configName);
  }

  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {
    if ($this->app->runningInConsole()) {
      // publishing the config
      $this->publishes([
        self::basePath("/config/{$this->configName}.php") => config_path("{$this->configName}.php"),
      ], "{$this->configName}-config");

      // registering the command
      $this->commands([
        Publish::class,
        GenerateJWTSecret::class,
      ]);
    }

    $this->configureLogChannel();
    $this->configureRedis();
    $this->registerRoutes();
    $this->configureMiddleware();
    $this->publishAssets();
  }

  /**
   * Register the routes.
   *
   * @return void
   */
  protected function registerRoutes()
  {
    $this->loadRoutesFrom(self::basePath('/routes/api.php'));
    $this->loadRoutesFrom(self::basePath('/routes/console.php'));
    $this->loadRoutesFrom(self::basePath('/routes/web.php'));
  }

  /**
   * Configure redis cache setting.
   */
  protected function configureRedisCache(string $name, string $connection) : void
  {
    $this->app->make("config")->set("cache.stores.{$name}", [
      "driver" => "redis",
      "connection" => $connection,
      "lock_connection" => "default",
    ]);
  }

  /**
   * Configure redis queue setting.
   */
  protected function configureRedisQueue(array $queue) : void
  {
    if (!$queue["enabled"]) {
      return;
    }

    $this->app->make("config")->set("queue.connections.{$queue["name"]}", [
      "driver" => "redis",
      "connection" => $queue["connection"],
      "queue" => $queue["queue"],
      "retry_after" => $queue["retry_after"],
      "block_for" => $queue["block_for"],
      "after_commit" => $queue["after_commit"],
    ]);
  }

  /**
   * Configure redis setting.
   */
  protected function configureRedis() : void
  {
    $redisConfig = config("{$this->configName}.redis");

    foreach ($redisConfig as &$redis) {
      $name = $redis["name"];
      $databases = $redis["databases"];

      foreach ($databases as $dbKey => &$database) {
        $configName = "{$name}_{$dbKey}";

        $this->app->make("config")->set("database.redis.{$configName}", [
          "client" => $redis["client"],
          "cluster" => $redis["cluster"],
          "url" => $redis["url"],
          "host" => $redis["host"],
          "username" => $redis["username"],
          "password" => $redis["password"],
          "port" => $redis["port"],
          "database" => $database,
          "persistent" => $redis["persistent"],
          "options" => $redis["options"],
        ]);

        if (Str::lower($dbKey) === "cache") {
          $this->configureRedisCache($name, $configName);
        }

        $this->configureRedisQueue($redis["queue_setting"]);
      }
    }
  }

  /**
   * Configure the logging channel.
   */
  protected function configureLogChannel() : void
  {
    $appName = config("app.name");

    $this->app->make("config")->set("logging.channels.{$this->getLogChannel()}", [
      "driver" => "daily",
      "path" => storage_path(sprintf("logs/{$this->getLogChannel()}-{$appName}.log")),
      "level" => "debug",
      "days" => 5,
      "name" => $this->getLogChannel(),
      "replace_placeholders" => true,
    ]);
  }

  protected function publishAssets()
  {
    $this->loadTranslationsFrom(self::basePath("/lang"), $this->configName);

    $this->publishes([
      self::basePath("/lang") => $this->app->langPath("vendor/{$this->configName}"),
    ], ["{$this->configName}-assets", "laravel-assets"]);
  }

  /**
   * Configure the middleware and priority.
   */
  protected function configureMiddleware() : void
  {
    app("router")->aliasMiddleware("dashboardBasicAuth", DashboardBasicAuth::class);
    app("router")->aliasMiddleware("apiCheckJwt", APICheckJWT::class);
    app("router")->aliasMiddleware("apiCheckAuthToken", APICheckAuthToken::class);
    app("router")->aliasMiddleware("redisCheckJwt", RedisCheckJWT::class);
    app("router")->pushMiddlewareToGroup("api", APIResponseFormat::class);
  }
}
