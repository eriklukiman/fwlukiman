<?php

declare(strict_types=1);

namespace Lukiman\Cores\Trigger\Engine;

use Lukiman\Cores\Exception\Base as ExceptionBase;
use Lukiman\Cores\Interfaces\Trigger;
use Socket as BaseSocket;

class Socket implements Trigger {
  /**
   * Timeout in seconds
   * 
   * @var int
   * */
  protected int   $connectionTimeout;

  /**
   * List of request headers
   * 
   * @var array
   * */
  protected array $headers        = [];

  /**
   * Request method(GET, POST, PUT, PATCH, DELETE)
   * 
   * @var string
   * */
  protected string $method;

  /**
   * Request URL segments
   * 
   * @var array
   * */
  protected array  $url;

  /**
   * Request raw body
   * 
   * @var string
   * */
  protected string $body;

  /**
   * End of line
   * 
   * @var string
   * */
  protected static string $eol = "\r\n";

  /**
   * Socket
   * 
   * @var \Socket
   * */
  private BaseSocket $socket;

  /**
   * Response headers
   * 
   * @var string
   * */
  protected string $responseHeaders;

  /**
   * Response body
   * 
   * @var string
   * */
  protected string $responseBody;

  /**
   * Constructor
   *
   * @param int $connectionTimeout
   * */
  public function __construct(int $connectionTimeout = 5) {
    $this->connectionTimeout = $connectionTimeout;
  }

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
  protected function fire(
    string $method,
    string $url,
    string|array $params = ''
  ): void {
    $this->setUrl($url);
    $this->setMethod($method);
    $this->setBody($params);
    $this->generateDefaultHeaders();

    if (!in_array($this->getMethod(), ['GET', 'DELETE'], true)) {
      $this->addHeaders(['Content-Length' => (string) strlen($this->getBody())], true);
    }
    $this->addHeaders(['Connection' => 'Close']);

    // ----------------------------------------------------------
    //  1. Build the socket address
    // ----------------------------------------------------------
    $newUrl = $this->getUrl();
    $isTls  = $newUrl['scheme'] === 'https';
    $host   = $newUrl['host'];
    $port   = $newUrl['port'];

    $address = gethostbyname($host); // quick DNS – keep it simple
    $domain  = filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
      ? AF_INET6 // IPv6
      : AF_INET; // IPv4

    // ----------------------------------------------------------
    //  2. Create + connect the socket
    // ----------------------------------------------------------
    $this->socket = socket_create($domain, SOCK_STREAM, $isTls ? SOL_TCP : SOL_TCP);
    if ($this->socket === false) {
      throw new ExceptionBase(socket_strerror(socket_last_error()));
    }

    // TLS context if needed
    if ($isTls) {
      // Allows the local TCP port to be re-used immediately after the socket
      // is closed, even if the connection is still in the TIME_WAIT state.
      socket_set_option($this->socket, SOL_TCP, SO_REUSEADDR, 1);

      // Read timeout: give up after N seconds if no data arrives.
      socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);

      // Write timeout: give up after N seconds if send buffer is full.
      socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);
    }

    if (!socket_connect($this->socket, $address, $port)) {
      throw new ExceptionBase(socket_strerror(socket_last_error($this->socket)));
    }

    // ----------------------------------------------------------
    //  3. Send the request
    // ----------------------------------------------------------
    $request = $this->buildRequest();
    $request = str_replace("\r\n", "\n", $request); // normalize once
    $written = 0;
    $len     = strlen($request);

    while ($written < $len) {
      $bytes = socket_write($this->socket, substr($request, $written));
      if ($bytes === false) {
        // get error message related to socket from last error
        throw new ExceptionBase(socket_strerror(socket_last_error($this->socket)));
      }
      $written += $bytes;
    }

    socket_close($this->socket);
    // reset headers
    $this->headers = [];
  }

  /**
   * Read the response of the request from socket
   * 
   * @return string
   * */
  protected function readResponse(): string {
    $respHeaders = $respBody = '';

    // status line
    $statusLine = $this->readLine();
    if ($statusLine === false) {
      throw new \RuntimeException('Failed to read status line');
    }

    // headers
    while (($line = $this->readLine()) !== false && trim($line) !== '') {
      $respHeaders .= $line;
    }

    // content-length / chunked detection
    $contentLength = 0;
    $chunked       = false;
    foreach (explode("\r\n", $respHeaders) as $h) {
      if (stripos($h, 'Content-Length:') === 0) {
        $contentLength = (int) trim(substr($h, 15));
      }
      if (stripos($h, 'Transfer-Encoding:') === 0 && stripos($h, 'chunked') !== false) {
        $chunked = true;
      }
    }

    // body
    if ($chunked) {
      while (true) {
        $chunkSizeLine = $this->readLine();
        $chunkSize     = hexdec(trim($chunkSizeLine));
        if ($chunkSize === 0) {
          $this->readLine(); // trailing CRLF
          break;
        }
        $respBody .= $this->readBytes($chunkSize);
        $this->readLine(); // CRLF after chunk
      }
    } elseif ($contentLength > 0) {
      $respBody = $this->readBytes($contentLength);
    } else {
      // Read as much as possible in one go, but never more than 8 KiB at a time, 
      // and never more than the number of bytes we still expect.
      while (($buf = socket_read($this->socket, 8192)) !== '' && $buf !== false) {
        $respBody .= $buf;
      }
    }

    return $respBody;
  }

  /**
   * Read a line from socket
   *
   * @return string|false
   * */
  private function readLine(): string|false {
    $line  = '';
    while (($char = socket_read($this->socket, 1)) !== false && $char !== '') {
      $line .= $char;
      if (substr($line, -2) === "\r\n") {
        return $line;
      }
    }
    return $line === '' ? false : $line;
  }

  /**
   * Read bytes from socket
   *
   * @param int $length
   * @return string
   * */
  private function readBytes(int $length): string {
    $data  = '';
    $left  = $length;

    while ($left > 0 && ($buf = socket_read(
      $this->socket,
      // Read as much as possible in one go, but never more than 8 KiB at a time, 
      // and never more than the number of bytes we still expect.
      min(8192, $left)
    )) !== false && $buf !== '') {
      $data .= $buf;
      $left -= strlen($buf);
    }
    return $data;
  }

  /**
   * Build request
   *
   * @return string
   * */
  protected function buildRequest(): string {
    $url = $this->getUrl();
    $path    = $this->getMethod() === 'GET'
      ? $url['path'] . (!empty($this->getBody()) ? '?' . $this->getBody() : '')
      : $url['path'];

    $req = $this->getMethod() . ' ' . $path . ' HTTP/1.1' . static::$eol;
    $req .= $this->headersToString() . static::$eol;
    if (!in_array($this->getMethod(), ['GET', 'DELETE'], true)) {
      $req .= $this->getBody() . static::$eol;
    }
    return $req;
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
   * Generate default headers
   *
   * @return void
   * */
  protected function generateDefaultHeaders(): void {
    $headers = [];
    $url     = $this->getUrl();

    $headers['Host']               = $url['host'];
    $headers['Content-Type']       = 'application/x-www-form-urlencoded';

    $this->addHeaders($headers);
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
}
