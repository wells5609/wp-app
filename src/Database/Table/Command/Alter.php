<?php

namespace WordPress\Database\Table\Command;

use WordPress\Database\Table\Column;
use WordPress\Database\Table\Command;

class Alter extends Command
{
	
	protected $addColumns = array();
	protected $dropColumns = array();
	protected $addKeys = array();
	protected $dropKeys = array();
	
	public function __invoke() {
		try {
			$this->invoke();
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
	
	public function addColumn($name) {
		if ($name instanceof Column) {
			$column = $name;
		} else {
			$column = new Column($name);
		}
		$this->addColumns[] = $column;
		return $column;
	}
	
	public function dropColumn($name) {
		if ($name instanceof Column) {
			$column = $name;
		} else {
			$column = $this->table->getColumn($name);
		}
		$this->dropColumns[] = $column;
		return $column;
	}
	
	public function addKey($key) {
		if (is_string($key)) {
			$this->addKeys[] = $key;
		}
	}
	
	public function dropKey($key) {
		if (is_string($key)) {
			$this->dropKeys[] = $key;
		}
	}
	
	protected function invoke() {
		foreach($this->addColumns as $column) {
			$this->exec('ADD COLUMN '.$this->addColumnSql($column));
		}
		foreach($this->dropColumns as $column) {
			if ($this->table->getColumn($column->name)) {
				$this->exec('DROP COLUMN `'.$column->name.'`');
			}
		}
		foreach($this->addKeys as $key) {
			$this->exec('ADD KEY (`'.$key.'`)');
		}
		foreach($this->dropKeys as $key) {
			$this->exec('DROP KEY (`'.$key.'`)');
		}
	}
	
	protected function exec($sql) {
		$this->table->getConnection()->query(
			'ALTER TABLE '.$this->table->getTableName()
			.PHP_EOL.'  '.$sql
		);
	}
	
	protected function addColumnSql(Column $col) {
		$sql = '`'.$col->name.'` '.$col->data_type;
		if ($col->max_length && is_numeric($col->max_length) && $col->max_length < 1000000000) {
			$sql.= '('.$col->max_length.')';
		} else if ($col->precision && is_numeric($col->precision)) {
			$sql .= '('.$col->precision.')';
		}
		if (! $col->is_nullable) {
			$sql .= ' NOT NULL';
		}
		if ($col->auto_increment) {
			$sql .= ' AUTO_INCREMENT';
		}
		if ($col->default) {
			$sql .= ' DEFAULT '.(is_string($col->default) ? '"'.$col->default.'"' : $col->default);
		}
		if (isset($col->after) && $col->after) {
			if ($col->after instanceof Column) {
				$col->after = $col->name;
			}
			if ($this->table->getColumn($col->after)) {
				$sql .= PHP_EOL.'    AFTER '.$col->after;
			}
		}
		return $sql;
	}
	
}
