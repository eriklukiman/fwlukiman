<?php
namespace Lukiman\Models\Master;

use \Lukiman\Cores\Model;

class Shoes extends Model {
    
    public function __construct () {
        parent::__construct();
		$this->table       = 'master_shoes';
		$this->prefix		= 'msho';
    }
    
}