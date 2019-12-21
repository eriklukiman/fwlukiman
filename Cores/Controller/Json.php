<?php
namespace Lukiman\Cores\Controller;
use \Lukiman\Cores\Controller;
use \Lukiman\Cores\Request;

class Json extends Controller {
	protected $_error = 0;
	protected $_errorCode = 0;
	protected $_errorMessage = '';
	
	public function beforeExecute() {
        parent::beforeExecute();
		if (!headers_sent()) {
			header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') ) );
			header( 'Access-Control-Allow-Credentials: true' );
			header('Content-type: application/json');
		}
	}
	
	public function execute($action = 'Index', array $params = null, Request $request = null) {
		parent::execute($action, $params, $request);
		
		$result = $this->getResult();
		if ( $this->_error OR (is_array($result) AND !isset($result['status'])) ) $result['status'] = array(
			'error'		=> $this->_error,
			'errorCode'	=> $this->_errorCode,
			'message'	=> $this->_errorMessage,
		);
		
		if (!empty($result)) {
			$caller = $this->request->getGetVars('callback');
			if (!empty($caller)) {
				if (empty($caller) OR ($caller == '?')) $caller = 'FrameworkCallback';
				// if (!headers_sent()) header('Content-type: text/javascript');
				return $caller . '(' . json_encode($result) . ');';
			} else return json_encode($result);
		}
	}
	
	protected function setError($data) {
		$this->_error = $data;
		return $this;
	}
	
	protected function setErrorCode($data) {
		$this->_errorCode = $data;
		return $this;
	}
	
	protected function setErrorMessage($data) {
		$this->_errorMessage = $data;
		return $this;
	}
	
	protected function setReturnCode($error) {
		$this->_error = ($error < 100) ? 0 : 1;
		$this->_errorCode = $error;
		$this->_errorMessage = StatusMessage::messageString($error);
		return $this;
	}
	
	protected function getError() {
		return array(
			'error'		=> $this->_error,
			'errorCode'	=> $this->_errorCode,
			'message'	=> $this->_errorMessage,
		);
	}
	
}