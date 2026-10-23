<?php

namespace Andre1502\NetUtilities\Security;

use Illuminate\Support\Facades\Log;

class PHP2ShellProtection
{
  /**
   * Determine whether a given PHP version is vulnerable to PHP2Shell (CVE-2026-109618 / CVE-2026-109619).
   *
   * Patched versions:
   * - PHP 8.2 >= 8.2.35
   * - PHP 8.3 >= 8.3.36
   * - PHP 8.4 >= 8.4.27
   * - PHP 8.5 >= 8.5.12
   */
  public static function isVulnerableVersion(?string $phpVersion = null): bool
  {
    $version = $phpVersion ?? PHP_VERSION;

    // Normalize any extra release tags (e.g. 8.5.11-1ubuntu1 -> 8.5.11)
    if (preg_match('/^(\d+\.\d+\.\d+)/', $version, $matches)) {
      $normalizedVersion = $matches[1];
    } else {
      $normalizedVersion = $version;
    }

    if (version_compare($normalizedVersion, '8.2.0', '<')) {
      // PHP < 8.2 is end-of-life and unpatched upstream
      return true;
    }

    if (version_compare($normalizedVersion, '8.2.0', '>=') && version_compare($normalizedVersion, '8.2.35', '<')) {
      return true;
    }

    if (version_compare($normalizedVersion, '8.3.0', '>=') && version_compare($normalizedVersion, '8.3.36', '<')) {
      return true;
    }

    if (version_compare($normalizedVersion, '8.4.0', '>=') && version_compare($normalizedVersion, '8.4.27', '<')) {
      return true;
    }

    if (version_compare($normalizedVersion, '8.5.0', '>=') && version_compare($normalizedVersion, '8.5.12', '<')) {
      return true;
    }

    return false;
  }

  /**
   * Check whether session upload progress is enabled in the current PHP runtime.
   */
  public static function isUploadProgressEnabled(): bool
  {
    $val = ini_get('session.upload_progress.enabled');

    return !in_array(strtolower((string) $val), ['0', 'off', 'false', 'no', ''], true);
  }

  /**
   * Get comprehensive security audit status for PHP2Shell.
   */
  public static function audit(?string $phpVersion = null): array
  {
    $version = $phpVersion ?? PHP_VERSION;
    $isVulnerableVersion = self::isVulnerableVersion($version);
    $uploadProgressEnabled = self::isUploadProgressEnabled();
    $isAtRisk = $isVulnerableVersion && $uploadProgressEnabled;

    $recommendations = [];

    if ($isVulnerableVersion) {
      $recommendations[] = 'Upgrade your PHP runtime to a patched version: PHP 8.2.35+, 8.3.36+, 8.4.27+, or 8.5.12+.';
      $recommendations[] = 'Restart PHP-FPM / web server processes after upgrading to clear worker memory.';
    }

    if ($uploadProgressEnabled) {
      $recommendations[] = 'Set "session.upload_progress.enabled = Off" in your php.ini or PHP-FPM pool configuration as an immediate mitigation.';
    }

    return [
      'php_version' => $version,
      'is_vulnerable_version' => $isVulnerableVersion,
      'upload_progress_enabled' => $uploadProgressEnabled,
      'is_at_risk' => $isAtRisk,
      'recommendations' => $recommendations,
    ];
  }

  /**
   * Log a security warning if the current environment is vulnerable and upload progress is active.
   */
  public static function verifyOrWarn(?string $logChannel = null): void
  {
    $audit = self::audit();

    if ($audit['is_at_risk']) {
      $message = sprintf(
        '[PHP2Shell Security Alert] Current PHP runtime (%s) has session.upload_progress enabled and is vulnerable to CVE-2026-109618 / CVE-2026-109619. Please upgrade PHP to 8.2.35+, 8.3.36+, 8.4.27+, or 8.5.12+, or set "session.upload_progress.enabled = Off".',
        $audit['php_version']
      );

      if (!empty($logChannel)) {
        Log::channel($logChannel)->warning($message);
      } else {
        Log::warning($message);
      }
    }
  }
}
