# net_utilities

![Packagist Version](https://img.shields.io/packagist/v/andre1502/net_utilities)

Library to help faster initialization Laravel project which include common functions, lang, exceptions, middleware, and routes.

## Installation

Run this script from command line inside project folder

```sh
composer require andre1502/net_utilities
composer install
```

Package will do auto publish for Lang data, to publish package config you can run this script from command line inside project folder

```sh
php artisan net-utilities:publish
```

Most of the config data can be overriden from project `.env`.

Config value

```php
<?php

return [
  "log_channel" => env("NET_UTILITIES_HTTP_REQUEST_LOG_CHANNEL", "RequestAPI"),
  "timeout" => env("NET_UTILITIES_HTTP_REQUEST_TIMEOUT", 60),
  "retry" => env("NET_UTILITIES_HTTP_REQUEST_RETRY_COUNT", 3),
  "retry_interval_ms" => env("NET_UTILITIES_HTTP_REQUEST_RETRY_INTERVAL_MS", 300),
  "timezone" => env("NET_UTILITIES_TIMEZONE", env("TIME_ZONE", "UTC")),
  "locale" => env("NET_UTILITIES_LOCALE", "en-US"),
  "date_format" => env("NET_UTILITIES_DATE_FORMAT", "Y-m-d"),
  "datetime_format" => env("NET_UTILITIES_DATETIME_FORMAT", "Y-m-d H:i:s"),
  "duration_format" => env("NET_UTILITIES_DURATION_FORMAT", "%H:%I:%S"),
  "decimal_precision" => env("NET_UTILITIES_DECIMAL_PRECISION", env("DECIMAL_PRECISION", 2)),
  "monthly_limit" => env("NET_UTILITIES_MONTHLY_LIMIT", env("MONTHLY_LIMIT", 6)),
  "db_trans_retry" => env("NET_UTILITIES_DB_TRANS_RETRY", env("DB_TRANS_RETRY", 5)),
  "page_size" => env("NET_UTILITIES_PAGE_SIZE", env("PAGE_SIZE", 20)),
  "apis" => [
    "check_jwt" => sprintf("%s/%s", env("NET_UTILITIES_CHECK_JWT_API_URL", ""), env("NET_UTILITIES_CHECK_JWT_API_PATH", "")),
  ],
  "internal_api_auth" => [
    "header" => env("NET_UTILITIES_INTERNAL_API_HEADER", env("INTERNAL_API_HEADER", "")),
    "token" => env("NET_UTILITIES_INTERNAL_API_TOKEN", env("INTERNAL_API_TOKEN", "")),
  ],
  "basic_auth" => [
    "username" => env("NET_UTILITIES_DASHBOARD_BASIC_AUTH_USERNAME", env("DASHBOARD_BASIC_AUTH_USERNAME", "admin")),
    "password" => env("NET_UTILITIES_DASHBOARD_BASIC_AUTH_PASSWORD", env("DASHBOARD_BASIC_AUTH_PASSWORD", "123456")),
    "allowed_ip" => env("NET_UTILITIES_DASHBOARD_ALLOWED_IP", env("DASHBOARD_ALLOWED_IP", "127.0.0.1")),
  ],
  "jwt_auth" => [
    "default" => [
      "secret" => env("NET_UTILITIES_JWT_SECRET", env("JWT_SECRET", "")),
      "ttl" => env("NET_UTILITIES_JWT_TTL", env("JWT_TTL", 20160)), // in minutes
    ],
    "check_jwt" => [
      "secret" => env("NET_UTILITIES_CHECK_JWT_SECRET", env("JWT_SECRET", "")),
    ],
  ],
  "redis_keys" => [
    "check_jwt" => "jwt_token:[user]",
  ],
  "redis" => [
    "check_jwt_redis" => [
      "name" => env("NET_UTILITIES_CHECK_JWT_REDIS", "check_jwt_redis"),
      "databases" => [
        "default" => env("NET_UTILITIES_CHECK_JWT_REDIS_DATABASE_DEFAULT", 0),
        "cache" => env("NET_UTILITIES_CHECK_JWT_REDIS_DATABASE_CACHE", 1),
      ],
      "queue_setting" => [
        "enabled" => env("NET_UTILITIES_CHECK_JWT_REDIS_QUEUE_ENABLED", false),
        "queue" => env("NET_UTILITIES_CHECK_JWT_REDIS_QUEUE", "default"),
        "retry_after" => env("NET_UTILITIES_CHECK_JWT_REDIS_QUEUE_RETRY_AFTER", 90),
        "block_for" => env("NET_UTILITIES_CHECK_JWT_REDIS_QUEUE_BLOCK_FOR", null),
        "after_commit" => env("NET_UTILITIES_CHECK_JWT_REDIS_QUEUE_AFTER_COMMIT", false),
      ],
      "client" => env("NET_UTILITIES_CHECK_JWT_REDIS_CLIENT", "phpredis"),
      "cluster" => env("NET_UTILITIES_CHECK_JWT_REDIS_CLUSTER", false),
      "url" => env("NET_UTILITIES_CHECK_JWT_REDIS_URL", ""),
      "host" => env("NET_UTILITIES_CHECK_JWT_REDIS_HOST", "127.0.0.1"),
      "username" => env("NET_UTILITIES_CHECK_JWT_REDIS_USERNAME", ""),
      "password" => env("NET_UTILITIES_CHECK_JWT_REDIS_PASSWORD", ""),
      "port" => env("NET_UTILITIES_CHECK_JWT_REDIS_PORT", "6379"),
      "persistent" => env("NET_UTILITIES_CHECK_JWT_REDIS_PERSISTENT", false), // Enable persistent connection
      "options" => [
        "cluster" => env("NET_UTILITIES_CHECK_JWT_REDIS_CLUSTER", "redis"),
        "prefix" => env("NET_UTILITIES_CHECK_JWT_REDIS_PREFIX", "jwt_redis:"),
      ],
    ],
  ],
];
```

### JWT

To generate jwt key:

```sh
php artisan net-utilities:jwt-key
```

To generate jwt key and overwrite existing:

```sh
php artisan net-utilities:jwt-key --force
```

To display current jwt key:

```sh
php artisan net-utilities:jwt-key --show
```

## Usage

### Functions

Functions are added as Traits for easy to use (without needed to add dependency injection inside constructor).

Consists of:

- ConfigTrait
- UtilsTrait
- HttpRequestTrait
- Commands Trait:

  - PrependsEnvironmentTrait
  - PrependsOutputTrait
  - PrependsTimestampTrait

Commands Trait are useful to include inside Laravel Console Commands to make logging more richer.

You can use Traits directly inside Laravel class, e.g.:

```php
...

use Andre1502\NetUtilities\Traits\Config;

...

class TestCall extends Command
{
  use Config;

  ...

  public function handle()
  {
    $this->info("configName: {$this->configName}");
  }
}
```

### Lang

Inside Laravel package also possible to include locales translation files which can be used inside package or Laravel project itself.

To access this translation:

```php
...

use Andre1502\NetUtilities\Traits\Config;

...

class TestCall extends Command
{
  use Config;

  ...

  public function handle()
  {
    $this->info(__("{$this->configName}::remark.SUCCESS"));
  }
}
```

### Exceptions

Exceptions are used to write log and shape the error output to user. Better to also include Response from Symfony to use standard Http Status code.

```php
public function __construct(
  int $httpStatus, // API http status
  ?string $errorCode, // error code which follow translation key
  array $args = [], // additional data when write to log.
  array $params = [], // to include data inside translation key (refer to $errorCode).
  ?string $logChannel = null // to write into different log channel.
)
```

```php
...

use Symfony\Component\HttpFoundation\Response;
use Andre1502\NetUtilities\Exceptions\APIErrorException;

...

class Testing
{
  ...

  public function tester() : void
  {
    throw new APIErrorException(Response::HTTP_BAD_REQUEST, "example error");
  }
}
```

### Middleware

Package will automatically iclude common middleware which need to have,

- APICheckJWT
- APICheckAuthToken
- APIResponseFormat
- DashboardBasicAuth

#### APICheckJWT && RedisCheckJWT

This middleware are used to authenticate user JWT to centralized API, it will return user data.

Package will automatically register to middleware kernel as `apiCheckJwt` and `redisCheckJwt`.

You can use it inside `route api`:

```php
...

use Illuminate\Support\Facades\Route;

...

Route::post('/test', 'testApi')->middleware("apiCheckJwt")->name('test');
Route::post('/test1', 'testApi')->middleware("redisCheckJwt")->name('test1');

...
```

You also need to setup `.env` file for this key:

```.env
NET_UTILITIES_CHECK_JWT_API_URL=
NET_UTILITIES_CHECK_JWT_API_PATH=
```

#### APICheckAuthToken

This middleware are used to authenticate internal API call.

Package will automatically register to middleware kernel as `apiCheckAuthToken`.

You can use it inside `route api`:

```php
...

use Illuminate\Support\Facades\Route;

...

Route::post('test', 'testApi')->middleware("apiCheckAuthToken")->name('test');

...
```

#### APIResponseFormat

This middleware are used to custom format API response globaly.

Package will automatically register to middleware kernel group for `api`.

#### DashboardBasicAuth

This middleware are used to create basic authentication to access package dashboard with authentication.

Package will automatically register to middleware kernel as `dashboardBasicAuth`.

You also need to setup `.env` file for this key:

```.env
NET_UTILITIES_DASHBOARD_BASIC_AUTH_USERNAME=
NET_UTILITIES_DASHBOARD_BASIC_AUTH_PASSWORD=
NET_UTILITIES_DASHBOARD_ALLOWED_IP=
```

or

```.env
DASHBOARD_BASIC_AUTH_USERNAME=
DASHBOARD_BASIC_AUTH_PASSWORD=
DASHBOARD_ALLOWED_IP=
```

Allowed IP also support more than one IP, you can separate it with comma, e.g.:

```.env
DASHBOARD_ALLOWED_IP=127.0.0.1,192.168.1.1
```

### Routes

System will register common route for check whether project has been deployed correctly (health-check) and console route to help clean log remotely.

Package also automatically register the routes, so no additional action need to do.
