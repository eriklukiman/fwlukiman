<?php
namespace Lukiman\Cores\Encryption\Engine;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Encryption as IEncryption;


class Openssl extends Base implements IEncryption {
    protected String $cipher = "AES-128-CTR";
    protected String $iv = "";
    protected String $key = "";
    protected int $options = 0;

	public function __construct (?array $config) {
		if (!empty($config['cipher'])) $this->cipher = $config['cipher'];
        if (!in_array(strtolower($this->cipher), openssl_get_cipher_methods())) {
            throw new ExceptionBase("Invalid encryption method !");
        }

		if (!empty($config['iv'])) $this->iv = $config['iv'];
		
        if (empty($config['key'])) {
            throw new ExceptionBase("No encryption key defined!");
        }

        $this->key = $config['key'];
        
        if (isset($config['options'])) $this->options = $config['options'];
		return $this;
	}
	
    public function encrypt (String $str) : String {
        if (!$this->validateIv()) $this->generateIv();
        return openssl_encrypt($str, $this->cipher, $this->key, $this->options, $this->iv);
    }

    public function decrypt (String $str) : String {
        return openssl_decrypt($str, $this->cipher, $this->key, $this->options, $this->iv);
    }

    protected function validateIv() : bool {
        return ($this->getIvLength() == strlen($this->iv));
    }

    protected function getIvLength(?String $cipher = null) : int {
        if (empty($cipher)) $cipher = $this->cipher;
        return openssl_cipher_iv_length($cipher);
    }

    protected function generateIv(?String $cipher = null) : String {
        if (empty($cipher)) $cipher = $this->cipher;
        $iv_len = $this->getIvLength($cipher);
        return $this->iv = openssl_random_pseudo_bytes($iv_len);
    }
}