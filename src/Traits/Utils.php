<?php

namespace Andre1502\NetUtilities\Traits;

use Andre1502\NetUtilities\Traits\Config;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Utils
{
  use Config;

  /**
   * Generate a random float between min and max.
   */
  public function frand(float|int $min, float|int $max, int $decimals = 0) : float
  {
    $scale = pow(10, $decimals);

    return mt_rand((int) ($min * $scale), (int) ($max * $scale)) / $scale;
  }

  /**
   * Round a float up to a given precision.
   */
  public function roundUp(float $number, int $precision) : float
  {
    $fig = pow(10, abs($precision));

    return ceil($number * $fig) / $fig;
  }

  /**
   * Round a float down to a given precision.
   */
  public function roundDown(float $number, int $precision) : float
  {
    $fig = pow(10, abs($precision));

    return floor($number * $fig) / $fig;
  }

  /**
   * Safe division returning float or 0 on division by zero.
   */
  public function floatdiv(float $numerator, float $denominator) : float
  {
    if ((float) $denominator == 0.0) {
      return 0.0;
    }

    return (float) ($numerator / $denominator);
  }

  /**
   * Validate and decode JSON string, returning structured error and data.
   */
  public function jsonValidate(string $string) : array
  {
    // decode the JSON data
    $result = json_decode($string, true, 512, JSON_BIGINT_AS_STRING);

    switch (json_last_error()) {
      case JSON_ERROR_NONE:
        $error = "";
        break;
      case JSON_ERROR_DEPTH:
        $error = "The maximum stack depth has been exceeded.";
        break;
      case JSON_ERROR_STATE_MISMATCH:
        $error = "Invalid or malformed JSON.";
        break;
      case JSON_ERROR_CTRL_CHAR:
        $error = "Control character error, possibly incorrectly encoded.";
        break;
      case JSON_ERROR_SYNTAX:
        $error = "Syntax error, malformed JSON.";
        break;
      case JSON_ERROR_UTF8:
        $error = "Malformed UTF-8 characters, possibly incorrectly encoded.";
        break;
      case JSON_ERROR_RECURSION:
        $error = "One or more recursive references in the value to be encoded.";
        break;
      case JSON_ERROR_INF_OR_NAN:
        $error = "One or more NAN or INF values in the value to be encoded.";
        break;
      case JSON_ERROR_UNSUPPORTED_TYPE:
        $error = "A value of a type that cannot be encoded was given.";
        break;
      default:
        $error = "Unknown JSON error occurred.";
        break;
    }

    if (!empty($error)) {
      $result = $string;
    }

    return [
      "error" => $error,
      "data" => $result,
    ];
  }

  /**
   * Prefix domain to given file URL field names in lists.
   */
  public function updateFileUrl(?array $lists, array $fileUrlFieldNames, string $serverDomain) : ?array
  {
    if (empty($lists)) {
      return $lists;
    }

    foreach ($lists as &$key) {
      foreach ($fileUrlFieldNames as $fileUrlFieldName) {
        if (!empty($key[$fileUrlFieldName])) {
          $key[$fileUrlFieldName] = sprintf("%s%s", $serverDomain, $key[$fileUrlFieldName]);
        }
      }
    }

    return $lists;
  }

  /**
   * Wrapper for updateFileUrl handling pagination or array structures.
   */
  public function wrapperFileUrl(?array $lists, array $fileUrlFieldNames, string $serverDomain) : ?array
  {
    if (empty($lists)) {
      return $lists;
    }

    if (!isset($lists["data"])) {
      return $this->updateFileUrl($lists, $fileUrlFieldNames, $serverDomain);
    }

    $lists["data"] = $this->updateFileUrl($lists["data"], $fileUrlFieldNames, $serverDomain);

    return $lists;
  }

  /**
   * Get client IP address supporting reverse proxies.
   */
  public function getClientIp(bool $type = false, bool $client = true) : string
  {
    $typeIndex = $type ? 1 : 0;
    $detectedIp = "0.0.0.0";

    $server = function_exists('request') ? request()->server->all() : $_SERVER;

    if ($client) {
      if (!empty($server["HTTP_CF_CONNECTING_IP"])) {
        $detectedIp = (string) $server["HTTP_CF_CONNECTING_IP"];
      } elseif (!empty($server["HTTP_X_FORWARDED_FOR"])) {
        $arr = explode(",", (string) $server["HTTP_X_FORWARDED_FOR"]);
        $pos = array_search("unknown", $arr, true);

        if ($pos !== false) {
          unset($arr[$pos]);
        }

        $detectedIp = trim($arr[0] ?? "");
      } elseif (!empty($server["HTTP_CLIENT_IP"])) {
        $detectedIp = (string) $server["HTTP_CLIENT_IP"];
      } elseif (!empty($server["HTTP_X_FORWARDED"])) {
        $detectedIp = (string) $server["HTTP_X_FORWARDED"];
      } elseif (!empty($server["HTTP_X_CLUSTER_CLIENT_IP"])) {
        $detectedIp = (string) $server["HTTP_X_CLUSTER_CLIENT_IP"];
      } elseif (!empty($server["HTTP_FORWARDED_FOR"])) {
        $detectedIp = (string) $server["HTTP_FORWARDED_FOR"];
      } elseif (!empty($server["HTTP_FORWARDED"])) {
        $detectedIp = (string) $server["HTTP_FORWARDED"];
      } elseif (!empty($server["REMOTE_ADDR"])) {
        $detectedIp = (string) $server["REMOTE_ADDR"];
      }
    } elseif (!empty($server["REMOTE_ADDR"])) {
      $detectedIp = (string) $server["REMOTE_ADDR"];
    }

    $long = sprintf("%u", ip2long($detectedIp));
    $ip = $long ? [$detectedIp, (int) $long] : ["0.0.0.0", 0];

    return (string) $ip[$typeIndex];
  }

  /**
   * Generate random numeric string of given length.
   */
  public function getRandomNumber(int $randNumLength = 8) : string
  {
    $randNum = "";
    for ($i = 0; $i < $randNumLength; $i++) {
      $randNum .= mt_rand(0, 9);
    }

    return $randNum;
  }

  /**
   * Generate a unique transaction ID.
   */
  public function getTransId(string $prefix = "", int $randNumLength = 8) : string
  {
    if (!empty($prefix)) {
      $tmpRandNumLength = $randNumLength;
      $prefix = Str::limit($prefix, 4);
      $randNumLength = $randNumLength - Str::length($prefix);

      if ($randNumLength < 0) {
        $randNumLength = $tmpRandNumLength;
      }

      if ($randNumLength > 8) {
        $randNumLength = 8;
      }
    }

    $randNum = $this->getRandomNumber($randNumLength);

    return sprintf("%s%s%s", $prefix, Carbon::now()->isoFormat("YYMMDDHHmmss"), $randNum);
  }

  /**
   * Convert timestamp to local configured timezone.
   */
  public function convertToLocalTimezone(string $dateTime, string $sourceTimezone, string $dateTimeFormat = "Y-m-d H:i:s") : string
  {
    if ((empty($dateTime)) || (empty($sourceTimezone))) {
      return $dateTime;
    }

    $localTimezone = $this->getTimezone();

    $shiftToSourceTimezone = Carbon::parse($dateTime)->shiftTimezone($sourceTimezone);
    $setToLocalTimezone = $shiftToSourceTimezone->copy()->setTimezone($localTimezone);

    return $setToLocalTimezone->format($dateTimeFormat);
  }

  /**
   * Convert timestamp to a specific timezone.
   */
  public function convertToSpecificTimezone(string $dateTime, string $destTimezone, string $dateTimeFormat = "Y-m-d H:i:s") : string
  {
    if ((empty($dateTime)) || (empty($destTimezone))) {
      return $dateTime;
    }

    $localTimezone = $this->getTimezone();

    $shiftToLocalTimezone = Carbon::parse($dateTime)->shiftTimezone($localTimezone);
    $setToSpecificTimezone = $shiftToLocalTimezone->copy()->setTimezone($destTimezone);

    return $setToSpecificTimezone->format($dateTimeFormat);
  }

  /**
   * Convert timestamp to a specific timezone unix timestamp.
   */
  public function convertToSpecificTimezoneTimestamp(string $dateTime, string $destTimezone) : int
  {
    if ((empty($dateTime)) || (empty($destTimezone))) {
      return Carbon::parse($dateTime)->timestamp;
    }

    $localTimezone = $this->getTimezone();

    $shiftToLocalTimezone = Carbon::parse($dateTime)->shiftTimezone($localTimezone);
    $setToSpecificTimezone = $shiftToLocalTimezone->copy()->setTimezone($destTimezone);

    return $setToSpecificTimezone->timestamp;
  }

  /**
   * Strip pagination links and metadata from array.
   */
  public function removePaginateUrlPath(array $data) : array
  {
    return Arr::except($data, ["path", "first_page_url", "last_page_url", "next_page_url", "prev_page_url", "links"]);
  }

  /**
   * Serialize mixed values to JSON or print_r.
   */
  public function printMixed(mixed $mixed) : string
  {
    $encoded = json_encode($mixed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return ($encoded !== false) ? $encoded : print_r($mixed, true);
  }

  /**
   * Convert byte size into human readable string.
   */
  public function convert(float $size) : string
  {
    if ($size <= 0) {
      return "0 b";
    }

    $unit = ["b", "kb", "mb", "gb", "tb", "pb"];
    $i = (int) min(count($unit) - 1, max(0, floor(log($size, 1024))));

    return sprintf("%s %s", round($size / pow(1024, $i), 2), $unit[$i]);
  }
}
