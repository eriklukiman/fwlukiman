<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Trigger implements Interfaces\Trigger {
    protected int $connectionTimeout;
    protected array $headers;
    protected String $method;
    protected array $url;
    protected String $body;

    protected static String $eol = "\r\n";

    public function __construct($connectionTimeout = 5) {
        $this->connectionTimeout = $connectionTimeout;
    }

	public function get(String $url, String|array $params = '') : void {
        $this->fire('GET', $url, $params);
    }
	
	public function post(String $url, String|array $params = '') : void {
        $this->fire('POST', $url, $params);
    }

	public function put(String $url, String|array $params = '') : void {
        $this->fire('PUT', $url, $params);
    }

	public function patch(String $url, String|array $params = '') : void {
        $this->fire('PATCH', $url, $params);
    }

	public function delete(String $url, String|array $params = '') : void {
        $this->fire('DELETE', $url, $params);
    }

    protected function fire($method, $url, $params) : void {
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setBody($params);
        $this->generateDefaultHeaders();

        if (!in_array($this->getMethod(), ['GET', 'DELETE'])) $this->addHeaders(['Content-Length' => strlen($this->getBody())], TRUE);
        $this->addHeaders(['Connection' => 'Close']);

        $newUrl = $this->getUrl();
        $scheme = $newUrl['scheme'] === 'https' ? 'ssl://' : '';
        $host   = $scheme . $newUrl['host'];

        $socket = fsockopen($host, $newUrl['port'], $errno, $errstr, $this->connectionTimeout);
        if (! $socket) {
            throw new ExceptionBase($errstr, $errno);
        }

        $req = $this->buildRequest();
        fwrite($socket, $req);
        fclose($socket);

    }

    protected function buildRequest() : String {
        $req = '';
        $req .= $this->headersToString() . static::$eol;
        if (!in_array($this->getMethod(), ['GET', 'DELETE'])) $req .= $this->getBody() . static::$eol;
        return $req;
    }

    protected function getUrl() : array {
        return $this->url;
    }

    protected function setUrl(String $url) : void {
        $this->url = parse_url($url);
        if (empty($this->url['scheme']) OR empty($this->url['host'])) {
            throw new ExceptionBase('URL is not valid!');
        }
        if (empty($this->url['port'])) $this->url['port'] = static::getDefaultPort($this->url['scheme']);
        if (empty($this->url['path'])) $this->url['path'] = '/';
    }


    protected function getBody() : String {
        return $this->body;
    }

    protected function setBody(String|array $params) : void {
        if (is_array($params)) $this->body = http_build_query($params);
        else $this->body = $params;
    }

    protected function getMethod() : String {
        return $this->method;
    }

    protected function setMethod(String $method) : void {
        $this->method = $method;
    }

    protected function getHeaders() : array {
        return $this->headers;
    }

    protected function addHeaders(array $newHeaders, $isOverwrite = false) : void {
        foreach ($newHeaders as $k => $v) {
            if (is_numeric($k)) $this->headers[] = $v;
            else if ($isOverwrite OR !array_key_exists($k, $this->headers)) $this->headers[$k] = $v;
        }
    }

    protected function generateDefaultHeaders() : void {
        $headers = [];
        $url = $this->getUrl();

        $path = $this->getMethod() === 'GET' ? $url['path'] . (!empty($this->getBody())? ('?' . $this->getBody()) : '') : $url['path'];

        $headers[] =  $this->getMethod() . ' ' . $path . ' HTTP/1.1';
        $headers['Host'] = $url['host'];
        //$headers['Accept'] = '*/*';
        //$headers['Accept-Encoding'] = 'deflate, gzip';
        //$headers['User-Agent'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36';
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $this->addHeaders($headers);
    }

    protected function headersToString() : String {
        $sHeaders = '';
        foreach($this->headers as $k => $v) {
            if (is_numeric($k)) $sHeaders .= $v . static::$eol;
            else $sHeaders .= $k . ': ' . $v . static::$eol;
        }
        return $sHeaders;
    }

    protected static function getDefaultPort(String $scheme) : int {
        switch ($scheme) {
            case 'https':
                $defaultPort = 443;
                break;

            case 'http':
                $defaultPort = 80;
                break;

            default:
                $defaultPort = 80;
        }

        return $defaultPort;
    }
}