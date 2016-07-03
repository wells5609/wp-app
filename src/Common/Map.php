<?php

namespace WordPress\Common;

use ArrayObject;
use RuntimeException;

/**
 * Map is a collection of key-value pairs.
 * 
 * @author wells
 *
 * @since 1.0
 */
class Map extends ArrayObject implements CollectionInterface
{

	public function __construct($data = array(), $flags = 0, $iterator_class = 'ArrayIterator') {
		parent::__construct($data, ArrayObject::ARRAY_AS_PROPS, $iterator_class);
	}

	public function setFlags($flags) {
		throw new RuntimeException("Invalid operation: Map flags are immutable.");
	}
	
	public function get($key) {
		return $this->offsetGet($key);
	}
	
	public function has($key) {
		return $this->offsetExists($key);
	}
	
	public function set($key, $value) {
		$this->offsetSet($key, $value);
		return $this;
	}
	
	public function remove($key) {
		$this->offsetUnset($key);
		return $this;
	}

	public function isEmpty() {
		return $this->count() === 0;
	}

	public function toArray() {
		return $this->getArrayCopy();
	}

	public function reverse() {
		return new static(array_reverse($this->getArrayCopy()));
	}
	
	public function each(callable $fn) {
		$objects = $this->getArrayCopy();
		array_walk($objects, $fn);
		$this->exchangeArray($objects);
		return $this;
	}

	public function map(callable $fn) {
		return new static(array_map($fn, $this->getArrayCopy()));
	}

	public function filter(callable $fn) {
		return new static(array_filter($this->getArrayCopy(), $fn));
	}

	public function slice($offset = 0, $number = 1) {
		return new static(array_slice($this->getArrayCopy(), $offset, $number));
	}

	public function select(array $where, $operator = 'AND') {
		return new static(wp_list_filter($this->getArrayCopy(), $where, $operator));
	}

	public function first(callable $fn) {
		foreach($this as $record) {
			if ($fn($record)) {
				return $record;
			}
		}
	}

	public function column($column, $index_column = null) {
		return wp_list_pluck($this->getArrayCopy(), $column, $index_column);
	}

	public function sort(callable $fn = null) {
		if ($fn) {
			$this->uasort($fn);
		} else {
			$this->asort();
		}
		return $this;
	}

}
