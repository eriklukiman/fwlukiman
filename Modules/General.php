<?php
namespace Lukiman\Modules;

use \Lukiman\Cores\Controller;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

abstract class General extends Controller\Json {
	
	public function do_Index($param) {
		throw new ExceptionBase('No Action defined!');
	}
	
	protected function getValueFromParameter ($type = 'get', $var = null) {
		return $this->request->{'get' . $type . 'Vars'}(!is_null($var) ? $var : '');
	}

	public function getValuesFromPost ($key = null) {
		return $this->request->getPostVars($key);
	}
	
}
