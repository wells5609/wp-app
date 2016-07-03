<?php

namespace WordPress\Model\Term;

use WordPress\Model\AbstractModel;

class Term extends AbstractModel
{
	
	/**
	 * The term id.
	 * 
	 * @var int
	 */
	public $term_id;
	
	/**
	 * The term name.
	 * 
	 * @var string
	 */
	public $name;
	
	/**
	 * The term slug.
	 * 
	 * @var string
	 */
	public $slug;
	
	/**
	 * The term group.
	 * 
	 * @var int
	 */
	public $term_group;
	
	/**
	 * Returns the term_id.
	 * 
	 * @return int
	 */
	public function getPrimaryKeyValue() {
		return $this->term_id;
	}
	
	/**
	 * Magic __get
	 * 
	 * @param string $var
	 * @return mixed
	 */
	public function __get($var) {
		if ('data' === $var) {
			return $this->getDataObject();
		}
	}
	
	/**
	 * Returns a stdClass with the term data.
	 * 
	 * @return \stdClass
	 */
	public function getDataObject() {
		$columns = $this->getColumnMap();
		$data = new \stdClass();
		foreach (array_keys($columns) as $column) {
			$data->$column = isset($this->$column) ? $this->$column : null;
		}
		return sanitize_term($data, $data->taxonomy, 'raw');
	}
	
	/**
	 * Sanitizes term fields, according to the filter type provided.
	 *
	 * @param string $filter Filter context. Accepts 'edit', 'db', 'display', 'attribute', 'js', 'raw'.
	 * @return void
	 */
	public function filter($filter = 'display') {
		sanitize_term($this, $this->taxonomy, $filter);
	}
	
}
