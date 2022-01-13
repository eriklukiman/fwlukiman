<?php
namespace Lukiman\Test;

use Assert\Assertion;
use Assert\AssertionFailedException;

use \Lukiman\Cores\Encryption as mEncrypt;

class Encryption extends Base {
	public function do_SimpleEncrypt() {
		$encrypt_engine = new mEncrypt([
            'key'   => 'testkey1234567890',
            'iv'    => 'this is test iv',
            'cipher'=> 'AES-128-CTR',
        ]);

        $original = "This is a simple encryption test";

        $encrypted = $encrypt_engine->encrypt($original);
        $decrypted = $encrypt_engine->decrypt($encrypted);

        Assertion::equal($original, $decrypted);

        return $decrypted;
		
	}
}
