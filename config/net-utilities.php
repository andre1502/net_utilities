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
      "secret" => env("NET_UTILITIES_CHECK_JWT_SECRET", env("NET_UTILITIES_JWT_SECRET", env("JWT_SECRET", ""))),
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
