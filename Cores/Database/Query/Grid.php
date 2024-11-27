<?php
namespace Lukiman\Cores\Database\Query;

use \Lukiman\Cores\{Database, Request};

class Grid extends Select {
	protected Request $request;
	protected ?int $_rows = null;
	protected ?int $_totalRows = null;
	protected ?int $_page = null;
	protected ?int $_totalPage = null;
	protected ?int $_rowsPerPage = null;
	protected int $_defaultPage = 1;
	protected int $_defaultRowsPerPage = 20;
	protected String $_pageName = 'page';
	protected String $_rowsPerPageName = 'max';

	public function __construct (String $table = '', ?Database $db = null) {
		parent::__construct($table, $db);
        $this->request = new Request();
		$this->_rowsPerPage = $this->_defaultRowsPerPage;
		$this->_page = $this->_defaultPage;
	}

	public function execute(?Database $db = null) : self {
		// parent::execute($db);
		$db = $this->getValidDb($db);
		if (is_array($this->_orderBy)) $this->_orderBy = implode(' , ', $this->_orderBy);
		if (is_array($this->_groupBy)) $this->_groupBy = implode(' , ', $this->_groupBy);
		if (is_array($this->_useHaving)) $this->_useHaving = implode(' , ', $this->_useHaving);

		$this->detectRowsPerPage();
		$this->detectPaging();
		$this->limit( ($this->_page - 1) * $this->_rowsPerPage, $this->_rowsPerPage);

		if (is_array($this->_join)) $this->_join = implode(' ', $this->_join);
		// Database::activate($setting);
		// print_r($this);
		$this->_dbStatement = Database::Select($db, $this->_table, $this->_columns, $this->_where, $this->_bindVars, $this->_join, $this->_orderBy, $this->_groupBy, $this->_useHaving, $this->_useLimit, true);
		// print_r($this->_dbStatement);
		// print_r($this);
		// $rs1 = Database::getInstance()->query('SELECT FOUND_ROWS() ');
		// echo $rs1->fetchColumn();
		$this->countData($db);
		$db->releaseConnection();
		// var_dump($this->_dbStatement->rowCount());
		// var_dump($this->_dbStatement->result_set);
		return $this;
	}

	public function detectPaging () : self {
		$get = $this->request->getGetVars();
		$post = $this->request->getPostVars();
		if (isset($post[$this->_pageName])) $this->_page = $post[$this->_pageName] + 0;
		else if (isset($get[$this->_pageName])) $this->_page = $get[$this->_pageName] + 0;
		if ($this->_page < 1) $this->_page = $this->_defaultPage;
		return $this;
	}

	public function detectRowsPerPage () : self {
		$get = $this->request->getGetVars();
		$post = $this->request->getPostVars();
		if (isset($post[$this->_rowsPerPageName])) $this->_rowsPerPage = $post[$this->_rowsPerPageName] + 0;
		else if (isset($get[$this->_rowsPerPageName])) $this->_rowsPerPage = $get[$this->_rowsPerPageName] + 0;
		if ($this->_rowsPerPage < 1) $this->_rowsPerPage = $this->_defaultRowsPerPage;
		return $this;
	}

	public function countData (?Database $db = null) : self {
		if (empty($this->_totalRows)) {
			$db = $this->getValidDb($db);
			$rs1 = $db->query('SELECT FOUND_ROWS() ');
			// $this->_rows = $this->count();
			$this->_rows = $this->_dbStatement->rowCount();
			if(empty($this->_rows) AND isset($this->_dbStatement->result_set)) $this->_rows = count($this->_dbStatement->result_set);
			$this->_totalRows = $rs1->fetchColumn() + 0;
			$this->_totalPage = ceil($this->_totalRows / $this->_rowsPerPage);
			if (empty($this->_totalRows) AND empty($this->_totalPage)) $this->_totalPage = 1;
		}
		return $this;
	}

	public function getRows() : ?int {
		if (is_null($this->_totalRows)) $this->countData();
		return $this->_rows;
	}

	public function getTotalRows() : ?int {
		if (is_null($this->_totalRows)) $this->countData();
		return $this->_totalRows;
	}

	public function getRowsPerPage() : ?int {
		return $this->_rowsPerPage;
	}

	public function getPage() {
		if (is_null($this->_totalPage)) $this->countData();
		return $this->_page;
	}

	public function getTotalPage() : ?int {
		if (is_null($this->_totalPage)) $this->countData();
		return $this->_totalPage;
	}

	public function getGridInfo () : array {
		return array(
			'page'			=> $this->getPage(),
			'itemPerPage'	=> $this->getRowsPerPage(),
			'totalPage'		=> $this->getTotalPage(),
			'data'			=> $this->getRows(),
			'totalData'		=> $this->getTotalRows(),
		);
	}

	public function setRequest(Request $req) : void {
		$this->request = $req;
	}

	public function reset() : self {
		parent::reset();
		$this->_rowsPerPage = $this->_defaultRowsPerPage;
		$this->_page = $this->_defaultPage;
		$this->_rows = 0;
		$this->_totalRows = 0;

		return $this;
	}

}
