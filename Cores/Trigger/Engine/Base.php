<?php

namespace Lukiman\Cores\Trigger\Engine;

use Lukiman\Cores\Interfaces\Trigger;
use Lukiman\Cores\Exception\Base as ExceptionBase;

abstract class Base implements Trigger {
  /**
   * Request URL segments
   * 
   * @var array
   * */
  protected array  $url;

  /**
   * Request method(GET, POST, PUT, PATCH, DELETE)
   * 
   * @var string
   * */
  protected string $method;

  /**
   * End of line
   * 
   * @var string
   * */
  protected static string $eol = "\r\n";

  /**
   * Request raw body
   * 
   * @var string
   * */
  protected string $body;

  /**
   * List of request headers
   * 
   * @var array
   * */
  protected array $headers        = [];

  /**
   * Make a GET request
   * 
   * @param string $url
   * @param string|array $params
   *
   * @return void
   * */
  public function get(
    string $url,
    string|array $params = ''
  ): void {
    $this->fire('GET', $url, $params);
  }

  /**
   * Make a POST request
   * 
   * @param string $url
   * @param string|array $params
   *
   * @return void
   * */
  public function post(
    string $url,
    string|array $params = ''
  ): void {
    $this->fire('POST', $url, $params);
  }

  /**
   * Make a PUT request
   * 
   * @param string $url
   * @param string|array $params
   *
   * @return void
   * */
  public function put(
    string $url,
    string|array $params = ''
  ): void {
    $this->fire('PUT', $url, $params);
  }

  /**
   * Make a PATCH request
   * 
   * @param string $url
   * @param string|array $params
   *
   * @return void
   * */
  public function patch(
    string $url,
    string|array $params = ''
  ): void {
    $this->fire('PATCH', $url, $params);
  }

  /**
   * Make a DELETE request
   * 
   * @param string $url
   * @param string|array $params
   *
   * @return void
   * */
  public function delete(
    string $url,
    string|array $params = ''
  ): void {
    $this->fire('DELETE', $url, $params);
  }

  /**
   * Send the request
   *
   * @param string $method
   * @param string $url
   * @param string|array $params
   * 
   * @return void
   * */
  abstract protected function fire(
    string $method,
    string $url,
    string|array $params = ''
  ): void;

  /**
   * Add request headers
   *
   * @param array $newHeaders
   * @param bool $isOverwrite
   *
   * @return void
   * */
  public function addHeaders(array $newHeaders, bool $isOverwrite = false): void {
    foreach ($newHeaders as $k => $v) {
      if (is_numeric($k)) {
        $this->headers[] = $v;
      } elseif ($isOverwrite || !array_key_exists($k, $this->headers)) {
        $this->headers[$k] = $v;
      }
    }
  }

  /**
   * Get request method(GET, POST, etc.)
   *
   * @return string
   * */
  protected function getMethod(): string {
    return $this->method;
  }

  /**
   * Set request method(GET, POST, etc.)
   *
   * @param string $method
   * @return void
   * */
  protected function setMethod(string $method): void {
    $this->method = $method;
  }

  /**
   * Get request headers
   * 
   * @return array
   * */
  protected function getHeaders(): array {
    return $this->headers;
  }

  /**
   * Get URL
   *
   * @return array
   * */
  protected function getUrl(): array {
    return $this->url;
  }

  /**
   * Get request body
   *
   * @return string
   * */
  protected function getBody(): string {
    return $this->body;
  }

  /**
   * Set request body
   *
   * @param string|array $params
   * @return void
   * */
  protected function setBody(string|array $params): void {
    $this->body = is_array($params) ? http_build_query($params) : $params;
  }

  /**
   * Set URL
   *
   * @param string $url
   * @return void
   * */
  protected function setUrl(string $url): void {
    $this->url = parse_url($url);
    if (empty($this->url['scheme']) || empty($this->url['host'])) {
      throw new ExceptionBase('URL is not valid!');
    }
    if (empty($this->url['port'])) {
      $this->url['port'] = static::getDefaultPort($this->url['scheme']);
    }
    if (empty($this->url['path'])) {
      $this->url['path'] = '/';
    }
  }

  /**
   * Convert headers to string
   *
   * @return string
   * */
  protected function headersToString(): string {
    $sHeaders = '';
    foreach ($this->headers as $k => $v) {
      if (is_numeric($k)) {
        $sHeaders .= $v . static::$eol;
      } else {
        $sHeaders .= $k . ': ' . $v . static::$eol;
      }
    }
    return $sHeaders;
  }

  /**
   * Get default port
   *
   * @param string $scheme
   * @return int
   * */
  protected static function getDefaultPort(string $scheme): int {
    return match ($scheme) {
      'https' => 443,
      default => 80,
    };
  }

  /**
   * Allow singleton
   *
   * @return bool
   * */
  public static function allowSingleton(): bool {
    return true;
  }
}
