<?php
namespace Lukiman\Cores\Encryption\Engine;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Encryption as IEncryption;


class Openssl_secure extends Openssl implements IEncryption {
    public function encrypt (String $str) : String {
        $encrypted = parent::encrypt($str);
        return bin2hex($this->iv) . $encrypted;
    }

    public function decrypt (String $str) : String {
        if (strlen($str) % 2) throw new ExceptionBase("Invalid encrypted string", 500); //string must be even
        if (strlen($str) < 32) throw new ExceptionBase("Invalid encrypted string", 500); //string must be at least 32 characters (16 for IV and 16 for encrypted string
        $iv_len = 2 * $this->getIvLength();
        $iv = hex2bin(substr($str, 0, $iv_len));
        $str = substr($str, $iv_len);
        return openssl_decrypt($str, $this->cipher, $this->key, $this->options, $iv);
    }
}