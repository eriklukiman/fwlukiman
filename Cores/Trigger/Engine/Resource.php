<?php
namespace Lukiman\Cores\Trigger\Engine;

use Lukiman\Cores\Exception\Base as ExceptionBase;
use Lukiman\Cores\Interfaces\Trigger;

class Resource extends Base implements Trigger {
    protected int $connectionTimeout;
    protected array $headers;
    protected String $method;
    protected array $url;
    protected String $body;

    protected static String $eol = "\r\n";

    public function __construct(int $connectionTimeout = 5) {
        $this->connectionTimeout = $connectionTimeout;
    }

    protected function fire(String $method, String $url, String|array $params = '') : void {
        $errno = 0;
        $errstr = '';
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setBody($params);
        $this->generateDefaultHeaders();

        if (!in_array($this->getMethod(), ['GET', 'DELETE'])) $this->addHeaders(['Content-Length' => strlen($this->getBody())], TRUE);
        $this->addHeaders(['Connection' => 'Close']);

        $newUrl = $this->getUrl();
        $scheme = $newUrl['scheme'] === 'https' ? 'ssl://' : '';
        $host   = $scheme . $newUrl['host'];

        //$socket = stream_socket_client($host, $errno, $errstr, $this->connectionTimeout);
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

    protected function readResponse($socket): string {
        $respHeaders = $respBody = '';
        // status line
        $statusLine = fgets($socket);
        if ($statusLine === false) {
            throw new \RuntimeException('Failed to read status');
        }

        // headers
        while (($line = fgets($socket)) !== false && trim($line) !== '') {
            $respHeaders .= $line;
        }

        $contentLength = 0;
        $chunked = false;
        foreach (explode("\r\n", $respHeaders) as $h) {
            if (stripos($h, 'Content-Length:') === 0) {
                $contentLength = (int)trim(substr($h, 15));
            }
            if (stripos($h, 'Transfer-Encoding:') === 0 && stripos($h, 'chunked') !== false) {
                $chunked = true;
            }
        }

        // body
        if ($chunked) {
            while (!feof($socket)) {
                $chunkSizeLine = fgets($socket);
                $chunkSize = hexdec(trim($chunkSizeLine));
                if ($chunkSize === 0) {
                    fgets($socket); // last CRLF
                    break;
                }
                $respBody .= fread($socket, $chunkSize);
                fgets($socket); // CRLF after chunk
            }
        } elseif ($contentLength > 0) {
            $respBody = fread($socket, $contentLength);
        } else {
            // server will close
            while (!feof($socket)) {
                $respBody .= fread($socket, 8192);
            }
        }
        // var_dump($respBody, $respHeaders);

        return $respBody;
    }
}
