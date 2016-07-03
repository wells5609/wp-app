<?php

namespace WordPress\Model;

class Query
{
	
	/**
	 * @var \WordPress\Model\ModelDefinitionInterface
	 */
	protected $definition;
	
	/**
	 * @var string
	 */
	protected $select;
	
	/**
	 * @var array
	 */
	protected $where = [];
	
	/**
	 * @var int
	 */
	protected $limit;
	
	/**
	 * @var string
	 */
	protected $orderBy;
	
	public function __construct(ModelDefinitionInterface $definition) {
		$this->definition = $definition;
	}
	
	public function select($selection) {
		if (is_array($selection)) {
			$selection = implode(', ', $selection);
		}
		$this->select = $selection;
		return $this;		
	}
	
	public function where($key, $value, $operator = '=') {
		if (is_string($value)) {
			$value = "'{$value}'";
		}
		$this->where[] = "`{$key}` {$operator} {$value}";
		return $this;
	}
	
	public function limit($num) {
		$this->limit = (int)$num;
		return $this;	
	}
	
	public function orderBy($field, $order = 'ASC') {
		$this->orderBy = "`{$this->getTableName()}`.`{$field}` ".strtoupper($order);
		return $this;
	}
	
	public function __toString() {
		
		$sql = "SELECT {$this->select} FROM {$this->getTableName()}";			
		
		if (! empty($this->where)) {
			$sql .= " WHERE ".implode(' AND ', $this->where);
		}
		
		if (isset($this->limit)) {
			$sql .= " LIMIT {$this->limit}";
		}
		
		if (isset($this->orderBy)) {
			$sql .= " ORDER BY {$this->orderBy}";
		}
		
		return $sql.';';
	}
	
	public function __call($func, array $args) {
		if (empty($args)) {
			throw new \RuntimeException("Missing arguments.");
		}
		return $this->where($key, $args[0]);
	}
	
	protected function getTableName() {
		return $this->definition->getTableName();
	}
	
}
