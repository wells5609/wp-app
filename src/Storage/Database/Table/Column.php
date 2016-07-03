<?php

namespace WordPress\Storage\Database\Table;

class Column implements \Serializable
{
	
	public $name;
	public $data_type;
	public $max_length;
	public $precision;
	public $is_nullable;
	public $is_key;
	public $is_primary_key;
	public $auto_increment;
	
	public function __construct($data) {
		$this->name				= $data->COLUMN_NAME;
		$this->data_type		= $data->DATA_TYPE;
		$this->max_length		= $data->CHARACTER_MAXIMUM_LENGTH ? (int)$data->CHARACTER_MAXIMUM_LENGTH : null;
		$this->precision		= $data->NUMERIC_PRECISION ? (int)$data->NUMERIC_PRECISION : null;
		$this->is_nullable		= 'YES' === $data->IS_NULLABLE;
		$this->is_key			= '' !== $data->COLUMN_KEY;
		$this->is_primary_key	= 'PRI' === $data->COLUMN_KEY;
		$this->auto_increment	= stripos($data->EXTRA, 'auto_increment') !== false;
	}
	
	public function __toString() {
		return $this->name;
	}
	
	public function serialize() {
		return serialize(get_object_vars($this));
	}
	
	public function unserialize($serial) {
		foreach(unserialize($serial) as $key => $value) {
			$this->$key = $value;
		}
	}
	
}
