<?php

namespace Andre1502\NetUtilities\Traits;

trait Config
{
  protected string $configName = "net-utilities";
  private string $cachedLogChannel;
  private int $cachedTimeout;
  private int $cachedRetry;
  private int $cachedRetryIntervalMs;
  private string $cachedTimezone;
  private string $cachedDatetimeFormat;
  private array $cachedJwtAuth;
  private string $cachedCheckJwtRedisName;
  private array $cachedRedisKeys;

  /**
   * @date 2024/04/15
   * @author Andre Lukito
   * @return string
   */
  public function getLogChannel() : string
  {
    if (!isset($this->cachedLogChannel)) {
      $this->cachedLogChannel = config("{$this->configName}.log_channel")
          ?? "RequestAPI";
    }

    return $this->cachedLogChannel;
  }

  /**
   * @date 2024/04/15
   * @author Andre Lukito
   * @return int
   */
  private function getTimeout() : int
  {
    if (!isset($this->cachedTimeout)) {
      $this->cachedTimeout = config("{$this->configName}.timeout")
          ?? 60;
    }

    return $this->cachedTimeout;
  }

  /**
   * @date 2024/04/15
   * @author Andre Lukito
   * @return int
   */
  private function getRetry() : int
  {
    if (!isset($this->cachedRetry)) {
      $this->cachedRetry = config("{$this->configName}.retry")
          ?? 3;
    }

    return $this->cachedRetry;
  }

  /**
   * @date 2024/04/15
   * @author Andre Lukito
   * @return int
   */
  private function getRetryIntervalMs() : int
  {
    if (!isset($this->cachedRetryIntervalMs)) {
      $this->cachedRetryIntervalMs = config("{$this->configName}.retry_interval_ms")
          ?? 300;
    }

    return $this->cachedRetryIntervalMs;
  }

  /**
   * @date 2024/04/15
   * @author Andre Lukito
   * @return string
   */
  public function getTimezone() : string
  {
    if (!isset($this->cachedTimezone)) {
      $this->cachedTimezone = config("{$this->configName}.timezone") ?? (config("app.timezone") ?? "UTC");
    }

    return $this->cachedTimezone;
  }

  /**
   * @date 2024/04/15
   * @author Andre Lukito
   * @return string
   */
  public function getDatetimeFormat() : string
  {
    if (!isset($this->cachedDatetimeFormat)) {
      $this->cachedDatetimeFormat = config("{$this->configName}.datetime_format")
          ?? "Y-m-d H:i:s";
    }

    return $this->cachedDatetimeFormat;
  }

  /**
   * @date 2024/08/19
   * @author Andre Lukito
   * @return array
   */
  public function getJwtAuth() : array
  {
    if (!isset($this->cachedJwtAuth)) {
      $this->cachedJwtAuth = config("{$this->configName}.jwt_auth");
    }

    return $this->cachedJwtAuth;
  }

  /**
   * @date 2024/08/19
   * @author Andre Lukito
   * @return string
   */
  public function getCheckJwtRedisName() : string
  {
    if (!isset($this->cachedCheckJwtRedisName)) {
      $this->cachedCheckJwtRedisName = config("{$this->configName}.redis.check_jwt_redis.name");
    }

    return $this->cachedCheckJwtRedisName;
  }

  /**
   * @date 2024/08/19
   * @author Andre Lukito
   * @return string
   */
  public function getRedisKeys() : array
  {
    if (!isset($this->cachedRedisKeys)) {
      $this->cachedRedisKeys = config("{$this->configName}.redis_keys");
    }

    return $this->cachedRedisKeys;
  }

  /**
   * @date 2024/08/19
   * @author Andre Lukito
   * @return string
   */
  public function getRedisKey(string $key) : string
  {
    $redisKeys = $this->getRedisKeys();

    if (!array_key_exists($key, $redisKeys)) {
      return "";
    }

    return $redisKeys[$key];
  }
}
