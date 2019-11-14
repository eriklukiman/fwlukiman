<?php
namespace Lukiman\Cores\Database\Query;

use \Lukiman\Cores\{Database, Request};

class Grid extends Select {
	protected $request;
	protected $_rows;
	protected $_totalRows;
	protected $_page;
	protected $_totalPage;
	protected $_rowsPerPage;
	protected $_defaultPage = 1;
	protected $_defaultRowsPerPage = 20;
	protected $_pageName = 'page';
	protected $_rowsPerPageName = 'max';
	
	public function __construct ($table = '') {
		parent::__construct($table);
        $this->request = new Request();
		$this->_rowsPerPage = $this->_defaultRowsPerPage;
		$this->_page = $this->_defaultPage;
	}
	
	public function execute($setting = 'default') {
		if (is_array($this->_orderBy)) $this->_orderBy = implode(' , ', $this->_orderBy);
		if (is_array($this->_groupBy)) $this->_groupBy = implode(' , ', $this->_groupBy);
		if (is_array($this->_useHaving)) $this->_useHaving = implode(' , ', $this->_useHaving);
		
		$this->detectRowsPerPage();
		$this->detectPaging();
		$this->limit( ($this->_page - 1) * $this->_rowsPerPage, $this->_rowsPerPage);
		
		if (is_array($this->_join)) $this->_join = implode(' ', $this->_join);
		Database::activate($setting);
		$this->_dbStatement = Database::Select($this->_table, $this->_columns, $this->_where, $this->_bindVars, $this->_join, $this->_orderBy, $this->_groupBy, $this->_useHaving, $this->_useLimit, true);
		// $rs1 = Database::getInstance()->query('SELECT FOUND_ROWS() ');
		// echo $rs1->fetchColumn();
		$this->countData();
		return $this;
	}
	
	public function detectPaging () {
		$get = $this->request->getGetVars();
		$post = $this->request->getPostVars();
		if (isset($post[$this->_pageName])) $this->_page = $post[$this->_pageName] + 0;
		else if (isset($get[$this->_pageName])) $this->_page = $get[$this->_pageName] + 0;
		if ($this->_page < 1) $this->_page = $this->_defaultPage;
		return $this;
	}
	
	public function detectRowsPerPage () {
		$get = $this->request->getGetVars();
		$post = $this->request->getPostVars();
		if (isset($post[$this->_rowsPerPageName])) $this->_rowsPerPage = $post[$this->_rowsPerPageName] + 0;
		else if (isset($get[$this->_rowsPerPageName])) $this->_rowsPerPage = $get[$this->_rowsPerPageName] + 0;
		if ($this->_rowsPerPage < 1) $this->_rowsPerPage = $this->_defaultRowsPerPage;
		return $this;
	}
	
	public function countData () {
		if (empty($this->_totalRows)) {
			$rs1 = Database::getInstance()->query('SELECT FOUND_ROWS() ');
			$this->_rows = $this->count();
			$this->_totalRows = $rs1->fetchColumn() + 0;
			$this->_totalPage = ceil($this->_totalRows / $this->_rowsPerPage);
		}
		return $this;
	}
	
	public function getRows() {
		if (empty($this->_totalRows)) $this->countData();
		return $this->_rows;
	}
	
	public function getTotalRows() {
		if (empty($this->_totalRows)) $this->countData();
		return $this->_totalRows;
	}
	
	public function getRowsPerPage() {
		return $this->_rowsPerPage;
	}
	
	public function getPage() {
		if (empty($this->_totalPage)) $this->countData();
		return $this->_page;
	}
	
	public function getTotalPage() {
		if (empty($this->_totalPage)) $this->countData();
		return $this->_totalPage;
	}
	
	public function getGridInfo () {
		return array(
			'page'			=> $this->getPage(),
			'itemPerPage'	=> $this->getRowsPerPage(),
			'totalPage'		=> $this->getTotalPage(),
			'total'			=> $this->getRows(),
			'totalData'		=> $this->getTotalRows(),
		);
	}
	
	public function reset() {
		parent::reset();
		$this->_rowsPerPage = $this->_defaultRowsPerPage;
		$this->_page = $this->_defaultPage;
		$this->_rows = 0;
		$this->_totalRows = 0;
		
		return $this;
	}
	
}
