<?php
namespace Lukiman\Cores\Encryption\Engine;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Encryption as IEncryption;


abstract class Base implements IEncryption {
    abstract public function encrypt (String $str) : String;

    abstract public function decrypt (String $str) : String;
}