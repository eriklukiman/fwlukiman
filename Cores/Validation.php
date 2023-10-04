<?php
class Validation {


    public function type_regex($regex = '') {
        foreach (self::$object_post as $kfile => $vfile){
			if( !preg_match($regex, $vfile['type']) ){
//				self::$message_error[$kfile] = "file type not valid";
				self::$message_error[$kfile] = StatusMessage::getPureMessage(170);
			}
		}
		return $this;
    }

    public function max_size($max_size = 0) {
        foreach (self::$object_post as $kfile => $vfile){
            /*** Multiple file max_size validation */
            if(is_array($vfile['size'])) {
                foreach ($vfile['size'] as $kk_vf => $vv_vf) {
                    if($vv_vf > $max_size) {
//                        self::$message_error[$kfile] = "file too large";
                        self::$message_error[$kfile] = StatusMessage::getPureMessage(172);;
                    }
                }
            } else {
                if($vfile['size'] > $max_size) {
//                    self::$message_error[$kfile] = "file too large";
                    self::$message_error[$kfile] = StatusMessage::getPureMessage(172);;
                }
            }
		}
        return $this;
    }

	/**
     * Validation cek exist
     * @param type $post_key is array field name
	 * @param type $table is table name
	 * @param type $field field check data
	 * @param type $fieldPrimary check id for update
     * @return \Validation
     */
	public function cek_exist($post_key, $table, $field, $fieldPrimary = ""){
		foreach ($post_key as $k_post => $v_post){
            $value = '';
            if(isset(self::$object_post[$v_post])) {
                $value = self::$object_post[$v_post];
            }
			if(isset(self::$object_post['hd1']) AND !empty(self::$object_post['hd1'])){
				$cek_exist = (array)Database_Query::Select($table)
						->where($field, $value)
						->where($fieldPrimary, self::$object_post['hd1'], "!=")
						->execute()
						->next();
			}else{
				$cek_exist = (array)Database_Query::Select($table)
						->where($field, $value)
						->execute()
						->next();
			}

			if(!empty($cek_exist[$field])){
//					self::$message_error[$v_post] = $value .' is already exist';
					self::$message_error[$v_post] = StatusMessage::getPureMessage(173, $value);
			}
		}
		return $this;
	}

	public function cek_exists($table = '', $post = array(), $where = array(), $fieldPrimary = '') {
		if (! empty($table) AND ! empty($post) ) {
			foreach($post AS $key => $val) {
				if ( isset(self::$object_post[$val]) ) {
					$exists = Database_Query::Select( $table )
							->where( $key, self::$object_post[$val] );

					if (! empty($where) ) {
						foreach($where AS $k => $v) {
							$operator	= (!empty($fieldPrimary) AND ($k == $fieldPrimary)) ? '!=' : '=';
							$exists 	= $exists
										->where( $k, $v, $operator );
						}
					}

					$exists = (array) $exists->execute()
							->next();

					if (! empty($exists[$key])) {
//						self::$message_error[$val] = self::$object_post[$val] .' is already exist';
						self::$message_error[$val] = StatusMessage::getPureMessage(173, self::$object_post[$val]);
					}
				}
			}
		}

		return $this;
	}

    /**
     * Function for check self exception
     * @param string $table
     * @param string $column
     * @param string $column_primary
     * @param string $key_post
     * @param string $id_primary
     * @return \Validation
     */
    public function check_exist_self_exception($table = '', $column = '', $column_primary = '', $key_post = '', $id_primary = '') {
        if(!empty($table) AND !empty($column) AND !empty($column_primary) AND !empty($key_post) AND !empty($id_primary)) {
            $value = '';
            if(isset(self::$object_post[$key_post])) {
                $value = self::$object_post[$key_post];
            }
            $check  = (array) Database_Query::Select($table)
                    ->columns(array(
                        'COUNT(1) as COUNT'
                    ))
                    ->where($column, $value)
                    ->where($column_primary, $id_primary, '!=')
                    ->execute()
                    ->next();
            if($check['COUNT'] != 0) {
//                self::$message_error[$key_post]     = self::$object_post[$key_post] . ' is already exist';
                self::$message_error[$key_post]     = StatusMessage::getPureMessage(173, self::$object_post[$key_post]);
            }
        }
        // return new Validation();
        return $this;
    }

	/**
     * Validation not empty post
     * @param type $post_key
     * @return \Validation
     */
    public static function not_empty($value) {
        $ret = '';
		if(empty($value)) {
			$ret = StatusMessage::messageString(186);
		}
        return $ret;
    }

    /**
     * Validation max character post
     * @param type $post_key
     * @param type $max_length
     * @return \Validation
     */
    public function max_length($post_key, $max_length = '') {
        /*** Check if array or not */
        if(is_array($post_key)) {
            /*** If variable is array recursive this function */
            foreach ($post_key as $k_post => $v_post) {
                $this->max_length($k_post, intval($v_post));
            }
        } else {
            if(isset(self::$object_post[$post_key])) {
                $data_post      = self::$object_post[$post_key];
            } else {
                $data_post      = '';
            }

            /*** Get length of character */
            $length         = strlen($data_post);
            if($length > $max_length) {
                self::$message_error[$post_key]      = $post_key . ' just allowed maximal ' . $max_length . ' character';
            }
        }
        // return new Validation();
        return $this;
    }

    /**
     * Validation digit character
     * @param type $post_key
     * @return \Validation
     */
    public static function digit($value) {
		$ret = '';
		$value = str_replace(' ', '', $value);
		if(!ctype_digit($value) AND !empty($value)) {
			$ret = StatusMessage::messageString(165);
		}
        return $ret;
    }

    /**
     * Validation alphabetical character
     * @param type $post_key
     * @return \Validation
     */
    public function alpha($post_key) {
        /*** Check if array or not */
        if(is_array($post_key)) {
            /*** If variable is array recursive this function */
            foreach ($post_key as $k_post => $v_post) {
                $this->alpha($v_post);
            }
        } else {
            if(isset(self::$object_post[$post_key])) {
                $data_post      = self::$object_post[$post_key];
            } else {
                $data_post      = '';
            }
            /*** Check type alpha */
            $data_post      = str_replace(' ', '', $data_post);
            if(!ctype_alpha($data_post) AND !empty($data_post)) {
//                self::$message_error[$post_key]      = $post_key . ' just allowed alphabetic character';
                self::$message_error[$post_key]      = StatusMessage::getPureMessage(166);
            }
        }
        // return new Validation();
        return $this;
    }

    /**
     * Validation alphanumeric character
     * @param type $post_key
     * @return \Validation
     */
    public function alnum($post_key) {
        /*** Check if array or not */
        if(is_array($post_key)) {
            /*** If variable is array recursive this function */
            foreach ($post_key as $k_post => $v_post) {
                $this->alnum($v_post);
            }
        } else {
            if(isset(self::$object_post[$post_key])) {
                $data_post      = self::$object_post[$post_key];
            } else {
                $data_post      = '';
            }
            /*** Check type alpha */
            $data_post      = str_replace(' ', '', $data_post);
            if(!ctype_alnum($data_post) AND !empty($data_post)) {
//                self::$message_error[$post_key]      = $post_key . ' just allowed alphanumeric character';
                self::$message_error[$post_key]      = StatusMessage::getPureMessage(167);
            }
        }
        // return new Validation();
        return $this;
    }

    /**
     * Validation alphanumeric character
     * @param type $post_key
     * @return \Validation
     */
    public function alnum_wo_space($post_key) {
        /*** Check if array or not */
        if(is_array($post_key)) {
            /*** If variable is array recursive this function */
            foreach ($post_key as $k_post => $v_post) {
                $this->alnum($v_post);
            }
        } else {
            if(isset(self::$object_post[$post_key])) {
                $data_post      = self::$object_post[$post_key];
            } else {
                $data_post      = '';
            }
            /*** Check type alpha */
            if(!ctype_alnum($data_post) AND !empty($data_post)) {
                self::$message_error[$post_key]      = $post_key . ' just allowed alphanumeric character and without space';
            }
        }
        // return new Validation();
        return $this;
    }

    /**
     * Validation email post
     * @param type $post_key
     * @return \Validation
     */
    public static function email($value) {
		$ret = '';
		$pattern = "/^[\_a-zA-Z0-9-]+(\.[\_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3})$/" ;
		if ( preg_match ( $pattern , $value ) ) {
			$domain = substr ( strrchr ( $value , "@" ) , 1 ) ;
			if(!self::isDomainAvailible( 'http://' . $domain ) ) {
				$ret = StatusMessage::messageString(161);
			}
		} else {
			$ret = StatusMessage::messageString(161);
		}
        return $ret;
    }
    
/*Mod By Filo*/
    /**
     * Validation email post
     * @param type $post_key
     * @return \Validation
     */
    public function decimal($post_key) {
        /*** Check if array or not */
        if(is_array($post_key)) {
            /*** If variable is array recursive this function */
            foreach ($post_key as $k_post => $v_post) {
                $this->decimal($v_post);
            }
        } else {
            if(isset(self::$object_post[$post_key]) AND !empty(self::$object_post[$post_key])) {
                $data_post      = self::$object_post[$post_key];
            } else {
                $data_post      = '';
            }
            /*** Check email */
            $pattern = '/^\d+(\.(\d+))?$/' ;
            if(!preg_match ( $pattern , $data_post ) ) {
	        self::$message_error[$post_key]      = 'invalid decimal';
            }
        }
        // return new Validation();
        return $this;
    }
/*Mod By Filo*/
    
    public function match($post_key) {
        foreach ($post_key as $val_1 => $val_2) {
            $param_1            = '';
            $param_2            = '';
            if(isset(self::$object_post[$val_1])) {
                $param_1        = self::$object_post[$val_1];
            }
            if(isset(self::$object_post[$val_2])) {
                $param_2        = self::$object_post[$val_2];
            }
            if(($param_1 != $param_2) AND !empty($param_1) AND !empty($param_2)) {
                self::$message_error[$val_1]      = $val_1 . ' not match as ' . $val_2;
                self::$message_error[$val_2]      = $val_2 . ' not match as ' . $val_1;
            }
        }
        // return new Validation();
        return $this;
    }

    /**
     * Function for validation datetime format
     * @param type $post_key
     * @return \Validation
     */
    public static function datetime($value) {
        $ret = '';

		if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value) AND !empty($value)) {
//                self::$message_error[$post_key]      = $post_key . ' invalid date format (YYYY-MM-DD)';
			$ret = StatusMessage::messageString(172);
		}
		return $ret;
    }

    /**
     * Function for validation date format
     * @param type $post_key
     * @return \Validation
     */
    public static function date($value) {
        $ret = '';

		if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $value) AND !empty($value)) {
//                self::$message_error[$post_key]      = $post_key . ' invalid date format (YYYY-MM-DD)';
			$ret = StatusMessage::messageString(172);
		}
		return $ret;
    }

     /**
     * Function for validation time format
     * @param type $post_key
     * @return \Validation
     */
    public static function time($value) {
        $ret = '';

		if (!preg_match("/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])/", $value) AND !empty($value)) {
			$ret = StatusMessage::messageString(175);
		}
		return $ret;
    }

    /**
     * Validation by preg_match
     * @param regex $regex
     * @param key post $post_key
     * @return \Validation
     */
    public function regex_match($regex = '', $post_key) {
        if(is_array($post_key)) {
            foreach ($post_key as $k_pk => $v_pk) {
                $this->regex_match($regex, $v_pk);
            }
        } else {
            if(isset(self::$object_post[$post_key])) {
                $data_post      = self::$object_post[$post_key];
            } else {
                $data_post      = '';
            }
            /*** Check by preg match */
            if (preg_match($regex, $data_post) AND !empty($data_post)) {
//                self::$message_error[$post_key]     = $post_key . ' invalid input';
                self::$message_error[$post_key]     = StatusMessage::getPureMessage(176);
            }
        }
        // return new Validation();
        return $this;
    }

    /**
     * Execute validation
     * @return boolean
     */
    public function validate() {
        if(!empty(self::$message_error)) {
            ksort(self::$message_error);
            return self::$message_error;
        } else {
            return true;
        }
    }


    /*** || ================ VALIDATION EMAIL AND DOMAIN ================ || */

    private static function isDomainAvailible ( $domain ) {
        if ( !filter_var ( $domain , FILTER_VALIDATE_URL ) ) {
            return false ;
        }
        $curlInit = curl_init ( $domain ) ;
        curl_setopt ( $curlInit , CURLOPT_CONNECTTIMEOUT , 10 ) ;
        curl_setopt ( $curlInit , CURLOPT_HEADER , true ) ;
        curl_setopt ( $curlInit , CURLOPT_NOBODY , true ) ;
        curl_setopt ( $curlInit , CURLOPT_RETURNTRANSFER , true ) ;
        $response = curl_exec ( $curlInit ) ;
        curl_close ( $curlInit ) ;
        if ( $response )
        return true ;

        return false ;
    }

    /*** || ================ VALIDATION EMAIL AND DOMAIN ================ || */


    /**
     * Function for validate minimum age
     * @param type $post_key_date key post for date input
     * @param type $minimum_age years of minimum age
     * @return \Validation
     */
    public function validate_age($post_key_date = '', $minimum_age = '') {
        if(!empty($post_key_date) AND !empty($minimum_age)) {
            $post       = '';
            if(isset(self::$object_post[$post_key_date])) {
                $post   = self::$object_post[$post_key_date];
            }
            $date       = new DateTime($post);
            $min_age    = new DateTime('now - ' . $minimum_age . 'years' );
            $check      = $date <= $min_age;
            if($check === false) {
                self::$message_error[$post_key_date]    = 'minimum age ' . $minimum_age . ' years';
            }
        }
        // return new Validation();
        return $this;
    }
    
    /**
     * Function for validate expired date
     * @param mixed $post_key_date
     * @return \Validation
     */
    public function validate_expired($post_key_date = '') {
        if(!empty($post_key_date)) {
            if(is_array($post_key_date)) {
                foreach ($post_key_date as $v_post) {
                    $this->validate_expired($v_post);
                }
            } else {
                $post           = '';
                $date           = '';
                if(isset(self::$object_post[$post_key_date])) {
                    $post       = self::$object_post[$post_key_date];
                    $date       = new DateTime($post);
                }
                $now            = new DateTime('now');
                $check          = $date >= $now;
                if($check === false) {
//                    self::$message_error[$post_key_date]    = 'this date was expired';
                    self::$message_error[$post_key_date]    = StatusMessage::getPureMessage(177);;
                }
            }
        }
        // return new Validation();
        return $this;
    }
    
    public function allow_type($type = '') {
        foreach (self::$object_post as $kfile => $vfile){
            if(is_array($vfile['type'])) {
                foreach ($vfile['type'] as $k_type => $v_type) {
                    if(!preg_match($type, $v_type) ){
                        self::$message_error[$kfile] = StatusMessage::getPureMessage(170);
                    }
                }
            } else {
                if(!preg_match($type, $vfile['type']) ){
                    self::$message_error[$kfile] = StatusMessage::getPureMessage(170);
                }
            }
        }
        // return new Validation();
        return $this;
    }
    
	public static function Form ($post = '') {
		if (!empty($post))
			return Validation_Form::post($post);
		else return new Validation_Form();
	}
	
}