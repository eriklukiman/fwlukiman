<?php
namespace Lukiman\Modules\Publics;

use \Lukiman\Cores\Model;
use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;
use \Lukiman\Modules\General;
use \Lukiman\Cores\Cache;
use \Lukiman\Cores\Authentication;
use \Lukiman\Cores\Data\Authentication as AuthData;

class Tes extends General {
    
    protected $mapping     = array();
	protected $mappingView = array();
    
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
	
	
}
