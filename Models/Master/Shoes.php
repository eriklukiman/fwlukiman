<?php
namespace Lukiman\Models\Master;

use \Lukiman\Cores\Model;

class Shoes extends Model {
    
    public function __construct () {
        parent::__construct();
		$this->_table       = 'master_shoes';
		$this->_prefix		= 'msho';
    }
    
}