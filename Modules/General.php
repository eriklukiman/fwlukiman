<?php
namespace Lukiman\Modules;

use \Lukiman\Cores\Controller;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

abstract class General extends Controller\Json {

	public function do_Index(mixed $param) : mixed {
		throw new ExceptionBase('No Action defined!');
	}

	protected function getValueFromParameter (String $type = 'get', ?String $var = null) : mixed {
		return $this->request->{'get' . $type . 'Vars'}(!is_null($var) ? $var : '');
	}

	public function getValuesFromPost (?String $key = null) : mixed {
		return $this->request->getPostVars($key);
	}

}
