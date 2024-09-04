<?php

namespace Andre1502\NetUtilities\Traits;

use Andre1502\NetUtilities\Traits\Config;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Utils
{
  use Config;

  /**
   * frand
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function frand($min, $max, $decimals = 0)
  {
    $scale = pow(10, $decimals);

    return mt_rand($min * $scale, $max * $scale) / $scale;
  }

  /**
   * roundUp
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function roundUp(float $number, int $precision) : float
  {
    $fig = pow(10, abs($precision));

    return ceil($number * $fig) / $fig;
  }

  /**
   * roundDown
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function roundDown(float $number, int $precision) : float
  {
    $fig = pow(10, abs($precision));

    return floor($number * $fig) / $fig;
  }

  /**
   * floatdiv
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function floatdiv(float $numerator, float $denominator) : float
  {
    try {
      return ((float) $denominator == 0) ? 0 : ((float) ((float) $numerator / (float) $denominator));
    } catch (\DivisionByZeroError $e) {
      return 0;
    }
  }

  /**
   * jsonValidate
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function jsonValidate(string $string) : array
  {
    // decode the JSON data
    $result = json_decode($string, true, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // switch and check possible JSON errors
    switch (json_last_error()) {
      case JSON_ERROR_NONE:
        $error = ""; // JSON is valid // No error has occurred
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
        // PHP >= 5.3.3
      case JSON_ERROR_UTF8:
        $error = "Malformed UTF-8 characters, possibly incorrectly encoded.";
        break;
        // PHP >= 5.5.0
      case JSON_ERROR_RECURSION:
        $error = "One or more recursive references in the value to be encoded.";
        break;
        // PHP >= 5.5.0
      case JSON_ERROR_INF_OR_NAN:
        $error = "One or more NAN or INF values in the value to be encoded.";
        break;
      case JSON_ERROR_UNSUPPORTED_TYPE:
        $error = "A value of a type that cannot be encoded was given.";
        break;
      default:
        $error = "Unknown JSON error occured.";
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
   * updateFileUrl
   *
   * @date     2024/04/15
   * @author   Andre Lukito
   * @return
   */
  public function updateFileUrl(?array $lists, array $fileUrlFieldNames, string $serverDomain) : array
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
   * wrapperFileUrl
   *
   * @date     2024/04/15
   * @author   Andre Lukito
   * @return
   */
  public function wrapperFileUrl(?array $lists, array $fileUrlFieldNames, string $serverDomain) : array
  {
    if (empty($lists)) {
      return $lists;
    }

    if (!isset($lists["data"])) {
      $lists = $this->updateFileUrl($lists, $fileUrlFieldNames, $serverDomain);

      return $lists;
    }

    $lists["data"] = $this->updateFileUrl($lists["data"], $fileUrlFieldNames, $serverDomain);

    return $lists;
  }

  /**
   * getClientIp
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function getClientIp(bool $type = false, bool $client = true) : string
  {
    $type = $type ? 1 : 0;
    static $ip = null;

    if ($ip !== null) {
      return $ip[$type];
    }

    if ($client) {
      if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
      } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $arr = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
        $pos = array_search("unknown", $arr);

        if ($pos !== false) {
          unset($arr[$pos]);
        }

        $ip = trim($arr[0]);
      } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
      } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED"];
      } elseif (isset($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
      } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_FORWARDED_FOR"];
      } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
        $ip = $_SERVER["HTTP_FORWARDED"];
      } elseif (isset($_SERVER["REMOTE_ADDR"])) {
        $ip = $_SERVER["REMOTE_ADDR"];
      }
    } elseif (isset($_SERVER["REMOTE_ADDR"])) {
      $ip = $_SERVER["REMOTE_ADDR"];
    }

    // 防止IP伪造
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? [$ip, $long] : ["0.0.0.0", 0];

    return $ip[$type];
  }

  /**
   * getRandomNumber
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
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
   * 生成trans_id
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
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
   * 轉換時間為Local時間
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @param   Carbon $dateTime
   * @param   string $sourceTimezone (e.g. Asia/Hong_Kong)
   * @return
   */
  public function convertToLocalTimezone(string $dateTime, string $sourceTimezone, string $dateTimeFormat = "Y-m-d H:i:s") : string
  {
    if ((empty($dateTime)) || (empty($sourceTimezone))) {
      return $dateTime;
    }

    $localTimezone = $this->getTimezone();

    //only ack datetime into specific timezone (e.g. score3th timezone (Asia/Hong_Kong))
    //initially parsed since $datetime will be string without any timezone indication
    $shiftToSourceTimezone = Carbon::parse($dateTime)->shiftTimezone($sourceTimezone);

    //change datetime into specific timezone (e.g. local timezone (Asia/Shanghai))
    //copy shifted datetime (already ack the timezone) and update datetime with timezone
    $setToLocalTimezone = $shiftToSourceTimezone->copy()->setTimezone($localTimezone);

    return $setToLocalTimezone->format($dateTimeFormat);
  }

  /**
   * 轉換時間為其他時間
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @param   Carbon $dateTime
   * @param   string $destTimezone (e.g. UTC)
   * @return
   */
  public function convertToSpecificTimezone(string $dateTime, string $destTimezone, string $dateTimeFormat = "Y-m-d H:i:s") : string
  {
    if ((empty($dateTime)) || (empty($destTimezone))) {
      return $dateTime;
    }

    $localTimezone = $this->getTimezone();

    //only ack datetime into local timezone (e.g. local timezone (Asia/Hong_Kong))
    //initially parsed since $datetime will be string without any timezone indication
    $shiftToLocalTimezone = Carbon::parse($dateTime)->shiftTimezone($localTimezone);

    //change datetime into specific timezone (e.g. score3th timezone (Asia/Shanghai))
    //copy shifted datetime (already ack the timezone) and update datetime with timezone
    $setToSpecificTimezone = $shiftToLocalTimezone->copy()->setTimezone($destTimezone);

    return $setToSpecificTimezone->format($dateTimeFormat);
  }

  /**
   * 轉換時間為其他時間
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @param   Carbon $dateTime
   * @param   string $destTimezone (e.g. UTC)
   * @return
   */
  public function convertToSpecificTimezoneTimestamp(string $dateTime, string $destTimezone) : int
  {
    if ((empty($dateTime)) || (empty($destTimezone))) {
      return Carbon::parse($dateTime)->timestamp;
    }

    $localTimezone = $this->getTimezone();

    //only ack datetime into local timezone (e.g. local timezone (Asia/Hong_Kong))
    //initially parsed since $datetime will be string without any timezone indication
    $shiftToLocalTimezone = Carbon::parse($dateTime)->shiftTimezone($localTimezone);

    //change datetime into specific timezone (e.g. score3th timezone (Asia/Shanghai))
    //copy shifted datetime (already ack the timezone) and update datetime with timezone
    $setToSpecificTimezone = $shiftToLocalTimezone->copy()->setTimezone($destTimezone);

    return $setToSpecificTimezone->timestamp;
  }

  /**
   * removePaginateUrlPath
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function removePaginateUrlPath(array $data) : array
  {
    return Arr::except($data, ["path", "first_page_url", "last_page_url", "next_page_url", "prev_page_url", "links"]);
  }

  /**
   * printMixed
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function printMixed(mixed $mixed) : string
  {
    try {
      return json_encode($mixed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch (Exception $e) {
      return print_r($mixed, true);
    }
  }

  /**
   * convert
   *
   * @date 2024/04/15
   * @author Andre Lukito
   * @return
   */
  public function convert(float $size) : string
  {
    $unit = ["b", "kb", "mb", "gb", "tb", "pb"];

    return sprintf("%s %s", @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2), $unit[$i]);
  }
}
