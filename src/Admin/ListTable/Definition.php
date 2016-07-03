<?php

namespace WordPress\Admin\ListTable;

use RuntimeException;

abstract class Definition
{
	protected $columns = array();
	protected $hiddenColumns = array();
	protected $sortableColumns = array();
	protected $itemsPerPage = 10;
	protected $enableAjax = false;
	protected $enableBulkActions = false;
	protected $enableCheckboxes = false;
	protected $bulkActions = array();
	protected $debug = true;
	
	private $_items;
	private $_table;
	
	abstract public function query();
	
	abstract public function singularName();
	
	abstract public function columns();
	
	public function pluralName() {
		return $this->singularName().'s';
	}
	
	public function setListTable(Table $table) {
		$this->_table = $table;
	}
	
	public function listTable() {
		return $this->_table;
	}
	
	public function hiddenColumns() {
		return $this->hiddenColumns;
	}
	
	public function sortableColumns() {
		return $this->sortableColumns;
	}
	
	public function itemsPerPage() {
		return $this->itemsPerPage;
	}
	
	public function enableAjax() {
		return $this->enableAjax;
	}
	
	public function enableBulkActions() {
		return $this->enableBulkActions;
	}
	
	public function bulkActions() {
		return $this->bulkActions;
	}
	
	public function processBulkAction($action) {
		throw new RuntimeException('You must overwrite '.__FUNCTION__.'() in '.get_class($this));
	}
	
	public function enableCheckboxes() {
		return $this->enableCheckboxes;
	}
	
	public function getCheckboxValue($item) {
		throw new RuntimeException('You must overwrite '.__FUNCTION__.'() in '.get_class($this));
	}
	
	public function debug() {
		return $this->debug;
	}
	
	public function displayBefore() {
		return '';
	}
	
	public function displayAfter() {
		return '';
	}
	
	public function formEnable() {
		return true;
	}
	
	public function formId() {
		return $this->pluralName().'-form';
	}
	
	public function formMethod() {
		return 'get';
	}
	
	final public function getItems() {
		if (! isset($this->_items)) {
			$this->_items = $this->query();
		}
		return $this->_items;
	}
	
	final public function getPagedItems($pagenum = 1) {
		$perPage = $this->itemsPerPage();
		$items = $this->getItems();
		$offset = ($pagenum - 1) * $perPage;
		return array_slice($items, $offset, $perPage);
	}

}