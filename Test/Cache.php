<?php
namespace Lukiman\Test;

use Assert\Assertion;
use Assert\AssertionFailedException;

use \Lukiman\Cores\Cache as mCache;

class Cache extends General {
	public function do_SimpleGet() {
		$cache = mCache::getInstance();
		$key = 'def';
		$val = $cache->get($key);
		if (empty($val)) {
			$cache->set($key, date('Y-m-d H:i:s'), 10);
			$val = $cache->get($key);
		}
		// var_dump($cache);
		// var_dump($val);
		return $val;
		
	}
}
