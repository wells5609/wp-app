<?php

namespace WordPress\Database\Table;

use WordPress\Attribute\SerializeProperties;

class Column implements \Serializable
{
	
	use SerializeProperties;
	
	public $name;
	public $data_type;
	public $default;
	public $max_length;
	public $precision;
	public $is_nullable;
	public $is_key;
	public $is_primary_key;
	public $auto_increment;
	public $data;
	
	public function __construct($data) {
		if (is_string($data)) {
			$this->name = $data;
		} else {
			$data 					= (object)$data;
			$this->name				= $data->COLUMN_NAME;
			$this->data_type		= $data->DATA_TYPE;
			$this->default			= $data->COLUMN_DEFAULT;
			$this->max_length		= $data->CHARACTER_MAXIMUM_LENGTH ? (int)$data->CHARACTER_MAXIMUM_LENGTH : null;
			$this->precision		= $data->NUMERIC_PRECISION ? (int)$data->NUMERIC_PRECISION : null;
			$this->is_nullable		= 'YES' === $data->IS_NULLABLE;
			$this->is_key			= '' !== $data->COLUMN_KEY;
			$this->is_primary_key	= 'PRI' === $data->COLUMN_KEY;
			$this->auto_increment	= stripos($data->EXTRA, 'auto_increment') !== false;
			$this->data 			= $data;
		}
	}
	
	public function __toString() {
		return $this->name;
	}
	
	public function toSqlString() {
		$sql = '`'.$this->name.'` '.$this->data_type;
		if ($this->max_length && $this->max_length < 1000) {
			$sql.= '('.$this->max_length.')';
		} else if ($this->precision) {
			$sql .= '('.$this->precision.')';
		}
		if (! $this->is_nullable) {
			$sql .= ' NOT NULL';
		}
		if ($this->auto_increment) {
			$sql .= ' AUTO_INCREMENT';
		}
		if ($this->default) {
			$sql .= ' DEFAULT '.(is_string($this->default) ? '"'.$this->default.'"' : $this->default);
		}
		return $sql;
	}
	
}
