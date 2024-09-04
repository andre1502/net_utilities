<?php

namespace Andre1502\NetUtilities\Traits;

use Andre1502\NetUtilities\Exceptions\FailedAPIConnectionException;
use Andre1502\NetUtilities\Exceptions\FailedAPIResponseException;
use Andre1502\NetUtilities\Exceptions\FailedAPIResultException;
use Andre1502\NetUtilities\Exceptions\InvalidHttpRequestMethodException;
use Andre1502\NetUtilities\Traits\Utils;
use Exception;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response as HttpClientResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request;

trait HttpRequest
{
  use Utils;

  protected string $logChannel;
  protected int $timeout;
  protected int $retry;
  protected int $retryIntervalMs;
  protected string $applicationJson = "application/json";
  protected string $applicationForm = "application/x-www-form-urlencoded";
  protected string $multipartFormData = "multipart/form-data";
  protected string $textPlain = "text/plain";

  private function init()
  {
    if (!isset($this->logChannel)) {
      $this->logChannel = $this->getLogChannel();
    }

    if (!isset($this->timeout)) {
      $this->timeout = $this->getTimeout();
    }

    if (!isset($this->retry)) {
      $this->retry = $this->getRetry();
    }

    if (!isset($this->retryIntervalMs)) {
      $this->retryIntervalMs = $this->getRetryIntervalMs();
    }
  }

  /**
   * setLogChannel
   *
   * @date     2024/04/15
   * @author   Andre Lukito
   * @param    string $logChannel
   * @return   void
   */
  public function setLogChannel(string $logChannel) : void
  {
    $this->logChannel = $logChannel;
  }

  /**
   * setTimeoutAndRetry
   *
   * @date     2024/04/15
   * @author   Andre Lukito
   * @param    ?int $timeout
   * @param    ?int $retry
   * @param    ?int $retryIntervalMs
   * @return   void
   */
  public function setTimeoutAndRetry(?int $timeout, ?int $retry, ?int $retryIntervalMs) : void
  {
    if (!is_null($timeout)) {
      $this->timeout = $timeout;
    }

    if (!is_null($retry)) {
      $this->timeout = $retry;
    }

    if (!is_null($retryIntervalMs)) {
      $this->timeout = $retryIntervalMs;
    }
  }

  /**
   * setCurrencyRequestUrl
   *
   * @date     2024/04/15
   * @author   Andre Lukito
   * @param    string $requestUrl
   * @param    ?string $currency
   * @return   string
   */
  public function setCurrencyRequestUrl(string $requestUrl, ?string $currency) : string
  {
    if (!empty($currency)) {
      $posIdx = strpos($requestUrl, ":currency");

      if ($posIdx !== false) {
        $requestUrl = Str::swap([":currency" => $currency], $requestUrl);
      }
    }

    return $requestUrl;
  }

  /**
   * requestAPI
   *
   * @date     2024/04/15
   * @author   Andre Lukito
   * @param    string $requestUrl
   * @param    ?string $token
   * @param    ?array $requestHeader
   * @param    ?array $requestData
   * @param    ?array $requestQuery
   * @param    string $requestMethod
   * @param    string $contentType = "application/json"
   * @return   array
   */
  public function requestAPI(string $requestUrl, ?string $token, ?array $requestHeader, ?array $requestData, ?array $requestQuery, string $requestMethod,
    string $contentType = "application/json") : array
  {
    $this->init();

    $result = [
      "status" => false,
      "status_code" => null,
      "response" => null,
      "exception" => null,
    ];

    $paramLog = [
      "request_url" => $requestUrl,
      "token" => Str::limit($token, 50),
      "request_header" => $requestHeader,
      "request_data" => $requestData,
      "request_query" => $requestQuery,
      "request_method" => $requestMethod,
      "content_type" => $contentType,
    ];

    Log::channel($this->logChannel)->info(sprintf("[%s:%s] paramLog: %s.", __METHOD__, __LINE__, $this->printMixed($paramLog)));

    $responseResult = null;

    try {
      $responseResult = Http::retry($this->retry, $this->retryIntervalMs)->timeout($this->timeout)->acceptJson();

      if (!empty($token)) {
        $responseResult = $responseResult->withToken($token);
      }

      if (!empty($requestHeader)) {
        $responseResult = $responseResult->withHeaders($requestHeader);
      }

      if (!empty($requestQuery)) {
        $responseResult = $responseResult->withQueryParameters($requestQuery);
      }

      switch ($contentType) {
        case $this->applicationJson:
          $responseResult = $responseResult->asJson();
          break;
        case $this->applicationForm:
          $responseResult = $responseResult->asForm();
          break;
        case $this->multipartFormData:
          $responseResult = $responseResult->asMultipart();
          break;
        default:
          break;
      }

      switch ($requestMethod) {
        case Request::METHOD_POST:
          $responseResult = $responseResult->post($requestUrl, $requestData);
          break;
        case Request::METHOD_PUT:
          $responseResult = $responseResult->put($requestUrl, $requestData);
          break;
        case Request::METHOD_GET:
          $responseResult = $responseResult->get($requestUrl, $requestData);
          break;
        default:
          throw new InvalidHttpRequestMethodException("Unknown Http request method [{$requestMethod}].");
      }

      Log::channel($this->logChannel)->info(sprintf("[%s:%s] - responseHeaders: %s", __METHOD__, __LINE__, $this->printMixed($responseResult->headers())));
      Log::channel($this->logChannel)->info(sprintf("[%s:%s] - responseResult: %s", __METHOD__, __LINE__, $responseResult->body()));

      $exception = null;

      if (!$responseResult->successful()) {
        $exception = $this->getException($responseResult);
      } else {
        $result["status"] = true;
      }

      $result["status_code"] = $responseResult->status();
      $result["response"] = $this->getResponseBody($responseResult);
      $result["exception"] = $exception;
    } catch (RequestException $ex) {
      Log::channel($this->logChannel)->info(sprintf("[%s:%s] - Failed API request [%s][%s].", __METHOD__, __LINE__,
        $ex->response->status(), print_r($ex->getMessage(), true)));

      $result["status"] = false;
      $result["status_code"] = $ex->response->status();
      $result["response"] = $this->getResponseBody($ex->response);
      $result["exception"] = $this->getException($ex->response);
    } catch (ConnectionException | GuzzleRequestException $ex) {
      Log::channel($this->logChannel)->info(sprintf("[%s:%s] - Failed API connection [%s][%s].", __METHOD__, __LINE__,
        $ex->getResponse()->getStatusCode(), print_r($ex->getMessage(), true)));

      throw new FailedAPIConnectionException(sprintf("Failed API connection [%s].", print_r($ex->getMessage(), true)));
    } catch (Exception $ex) {
      throw $ex;
    }

    return $result;
  }

  /**
   * getResponseBody
   *
   * @date     2024/06/19
   * @author   Andre Lukito
   * @param    HttpClientResponse $responseResult
   * @return   mixed
   */
  private function getResponseBody(HttpClientResponse $responseResult) : mixed
  {
    $responseContentType = $responseResult->header("Content-Type");
    $responseBody = $responseResult->body();

    if (Str::contains($responseContentType, $this->applicationJson)) {
      $responseBody = $responseResult->json();
    }

    return $responseBody;
  }

  /**
   * getException
   *
   * @date     2024/04/15
   * @author   Andre Lukito
   * @param    HttpClientResponse $responseResult
   * @return   mixed
   */
  private function getException(HttpClientResponse $responseResult) : mixed
  {
    $exception = null;

    try {
      $responseJson = $responseResult->json();

      if ((isset($responseJson["result"])) && (isset($responseJson["result"]["code"])) && (isset($responseJson["result"]["message"]))) {
        $exception = new FailedAPIResultException(sprintf("Failed API result [%s][%s].", $responseJson["result"]["code"], $responseJson["result"]["message"]));
      } else {
        $exception = new FailedAPIResponseException(sprintf("Failed API response [%s].", $responseResult->body()));
      }
    } catch (Exception $ex) {
      $exception = new FailedAPIResponseException(sprintf("Failed API response [%s].", $responseResult->body()));
    }

    return $exception;
  }
}
