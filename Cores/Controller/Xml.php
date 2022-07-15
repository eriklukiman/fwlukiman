<?php
namespace Lukiman\Cores\Controller;
use Spatie\ArrayToXml\ArrayToXml;
use \Lukiman\Cores\Controller;
use \Lukiman\Cores\Request;

class Xml extends Controller {
	protected $_error = 0;
	protected $_errorCode = 0;
	protected $_errorMessage = '';
	protected String $xmlEncoding = '';
	protected String $xmlVersion = '1.0';
	protected array $rootAttributes = [];
	protected String $numericTagPrefix = '';
	protected bool $prettify = true;
	protected bool $useXmlDeclaration = true;
	protected array $processingInstruction = [];
	
	public function execute($action = 'Index', array $params = null, Request $request = null) {
		parent::execute($action, $params, $request);
		
		$this->addHeaders(array(
			'Access-Control-Allow-Credentials'	=> 'true',
			'Content-type'						=> 'text/xml',
		));
		
		$result = $this->getResult();
		if ( $this->_error OR (is_array($result) AND !isset($result['status'])) ) $result['status'] = array(
			'error'		=> $this->_error,
			'errorCode'	=> $this->_errorCode,
			'message'	=> $this->_errorMessage,
		);
		
		$xml = new ArrayToXml($result, $this->rootAttributes, true, $this->xmlEncoding, $this->xmlVersion);
		if ($this->prettify) $xml->prettify();
		if (!$this->useXmlDeclaration) $xml->dropXmlDeclaration();
		if (!empty($this->processingInstruction)) {
			foreach ($this->processingInstruction as $kI => $vI) $xml->addProcessingInstruction($kI, $vI);
		}
		return $xml->toXml();
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
	
	protected function getError() {
		return array(
			'error'		=> $this->_error,
			'errorCode'	=> $this->_errorCode,
			'message'	=> $this->_errorMessage,
		);
	}
	
}