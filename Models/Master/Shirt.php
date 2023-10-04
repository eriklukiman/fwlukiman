<?php
namespace Lukiman\Models\Master;

use \Lukiman\Cores\Model;

class Shirt extends Model {
    
    public function __construct () {
        parent::__construct();
		$this->_table       = 'master_shirt';
		$this->_prefix		= 'mshi';
    }
    
}