<?php
namespace Lukiman\Modules\Publics;

use Assert\Assertion;
use Assert\AssertionFailedException;

use \Lukiman\Cores\Model;
use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;
use \Lukiman\Modules\General;
use \Lukiman\Cores\Cache;
use \Lukiman\Cores\Authentication;
use \Lukiman\Cores\Authorization\Role;
use \Lukiman\Cores\Authorization\Permission;

class Tes extends General {
    
    protected $mapping     = array();
	protected $mappingView = array();
	protected $dataId;
    
    protected $table_name   ;

    public function beforeExecute () {
        parent::beforeExecute () ;
		
		$this->dataId = 'userId';
		
		
		$this->table_name   = 'test';
    }
    
	// public function do_Index ($param) {
	
	// }
	
	public function do_Insert ($param) {
		$get = $this->getValueFromParameter('get');
		$post = $this->getValuesFromPost();
		$body = $this->request->getBody();
		$method = $this->request->getMethod();
		// print_r($this->request->getBody());
		// print_r($_SERVER);
		// print_r($_SERVER['PATH_INFO']);

		$db = Database::getInstance();
		Database_Query::Insert($this->table_name)
		->data(array(
			'testTime'	=> 'NOW()',
			'testGet'	=> print_r($get, true),
			'testPost'	=> print_r($post, true),
			'testBody'	=> print_r($body, true),
			'testHeader'=> print_r($this->request->getHeaders(), true),
			'testMethod'=> $method,
		))
		->execute($db);
		
		return array('done');
		/*return array(
			'testTime'	=> 'NOW()',
			'testGet'	=> ($get),
			'testPost'	=> ($post),
			'testBody'	=> ($body),
			'testHeader'=> ($this->request->getHeaders()),
			'testMethod'=> $method,
		);*/
	}
	
	public function do_Param ($param) {
		$get = $this->getValueFromParameter('get');
		$post = $this->getValuesFromPost();
		$body = $this->request->getBody();
		$method = $this->request->getMethod();


		return array(
			'testTime'	=> 'NOW()',
			'testGet'	=> ($get),
			'testPost'	=> ($post),
			'testBody'	=> ($body),
			'testHeader'=> ($this->request->getHeaders()),
			'testMethod'=> $method,
		);
	}
	
	public function do_Select ($param) {
		$shoes = Model::Load('Master\\Shoes');
		
		$q = Database_Query::Grid($shoes->getTable());
		$q->setRequest($this->request);
		
		$db = Database::getInstance();
		$data = $q->execute($db);
		
		$ret = array();
		$cnt = 0;
		while ($v = $data->next()) {
			$v = (array) $v;
			$ret['data'][] = $v; //array(
			$cnt++;
		}
		$ret['pagination'] = $q->getGridInfo();
		// $db->releaseConnection();
		return $ret;
	}
	
	public function do_Summarized($param) {
		return [
			'param' 	=> $this->do_Param($param),
			'select'	=> $this->do_Select($param)
		];
	}
	
	public function do_error($param) {
		// $shoes = Model::Load('Master\\Shoes1');
		$q = Database_Query::SELECT('users1');
		$db = Database::getInstance();
		$q->execute($db);
		
		return null;
	}
	
	public function do_Cache() {
		$cache = Cache::getInstance();
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
	
	public function do_Auth() {
		// $config = ['provider' => 'google'];
		// $auth = new Authentication($config);
		$auth = new Authentication();
		$token = '';
		$get = $this->getValueFromParameter('get');
		if (!empty($get['token'])) $token = $get['token'];
		
		// $a = new AuthData();
		// echo $a->getName();
		// $a->setName('test123');
		// $a->setEmail('ddd');
		// var_dump($a);
		// echo $a->getName();
		
		$auth->authWithToken($token);
	
		$cred = $auth->getCredentials();
		print_r($cred);
		if (!empty($cred)) print_r($cred->getUserName());
		// var_dump($auth);
		// $auth->revokeAuthentication();
		return ($auth->isAuthenticated() ? 'OK' : 'Failed');

	}
	
	public function do_Authorization() {
		echo '<pre>';
		$auth = new Permission("master", ['read', 'MoDify']);
		$auth1 = new Permission("master", ['write', 'modify']);
		$auth2 = new Permission("print_report", ['read', 'view']);
		$auth3 = new Permission();
		$auth4 = new Permission('master_barang', ['add', 'delete']);
		// var_dump($auth->canModify());
		// var_dump($auth->getname());
		// var_dump($auth->isAuthorized('read'));
		// $auth->add('modify1');
		// $auth->remove('modify');
		// var_dump($auth);
		// $a =  [1,2, 7];
		// $b = [3, 1];
		// var_dump(array_unique(array_merge($a,$b)));
		
		$authL = new Role();
		$role2 = new Role('admin');
		$authL->add($auth)->add($auth2)->add($auth3);
		// $authL->add($auth1)->remove($auth3);
		$role2->add($auth1)->remove($auth3)->add($auth4);
		$a = $authL->moduleMaster();
		var_dump($a);
		try {
			// Assertion::false(true, "not false");
			var_dump($authL->moduleMaster()->canModify());
			Assertion::true($authL->moduleMaster()->canModify());
			var_dump($authL->moduleMaster()->canView());
			Assertion::false($authL->moduleMaster()->canView());
			var_dump($authL->moduleMaster1()->canModify());
			Assertion::false($authL->moduleMaster1()->canModify());
			var_dump($authL->moduleMaster1()->canView());
			Assertion::true($authL->moduleMaster1()->canView());
			// var_dump($authL->modulePrint_Report());
			var_dump($authL->modulePrint_Report()->canView());
			Assertion::true($authL->modulePrint_Report()->canView());
		}  catch(AssertionFailedException $e) {
			// error handling
			$det = $e->getTrace();
			echo "\n[ERROR] " . $e->getMessage() . " In file " . $det[1]['file'] . ' line ' . $det[1]['line'] . PHP_EOL . PHP_EOL;
			// var_dump($e->getValue());
			// echo "\n[ERROR] File " . $det[1]['file'] . ' line ' . $det[1]['line'] . PHP_EOL . PHP_EOL;
			return;
			// print_r($e);
			// $e->getValue(); // the value that caused the failure
			// $e->getConstraints(); // the additional constraints of the assertion.
		}
		// $authL->remove($auth1);
		$authL->combine($role2);
		print_r($role2);
		print_r($authL);

	}
	
	
}
