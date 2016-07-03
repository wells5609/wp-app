<?php

namespace WordPress\Common;

use ArrayIterator;
use IteratorAggregate;

/**
 * Collection represents a list of items.
 * 
 * @author wells
 * 
 * @since 1.0
 */
class Collection implements IteratorAggregate, CollectionInterface
{

	protected $records;

	public function __construct(array $records) {
		$this->records = $records;
	}

	public function isEmpty() {
		return empty($this->records);
	}

	public function toArray() {
		return $this->records;
	}

	public function reverse() {
		return new static(array_reverse($this->records));
	}
	
	public function each(callable $fn) {
		array_walk($this->records, $fn);
		return $this;
	}

	public function map(callable $fn) {
		return new static(array_map($fn, $this->records));
	}

	public function filter(callable $fn) {
		return new static(array_filter($this->records, $fn));
	}

	public function slice($offset, $number = null) {
		return new static(array_slice($this->records, $offset, $number));
	}

	public function select(array $where, $operator = 'AND') {
		return new static(wp_list_filter($this->records, $where, $operator));
	}

	public function first(callable $fn) {
		foreach($this->records as $record) {
			if ($fn($record)) {
				return $record;
			}
		}
	}
	
	public function column($column, $index_column = null) {
		return wp_list_pluck($this->records, $column, $index_column);
	}

	public function sort(callable $fn = null) {
		if ($fn) {
			uasort($this->records, $fn);
		} else {
			asort($this->records);
		}
		return $this;
	}
	
	public function count() {
		return count($this->records);
	}

	public function getIterator() {
		return new ArrayIterator($this->records);
	}

	public function serialize() {
		return serialize($this->records);
	}

	public function unserialize($serial) {
		$this->records = unserialize($serial);
	}

}
