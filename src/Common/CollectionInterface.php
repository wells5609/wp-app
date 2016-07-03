<?php

namespace WordPress\Common;

use Traversable;
use Countable;
use Serializable;

/**
 * Collection represents a list of items.
 *
 * @author wells
 *
 * @since 1.0
 *
 * @version 1.0
 */
interface CollectionInterface extends Traversable, Countable, Serializable
{

	public function isEmpty();

	public function toArray();

	public function reverse();
	
	public function each(callable $fn);

	public function map(callable $fn);

	public function filter(callable $fn);

	public function slice($offset = 0, $number = 1);

	public function select(array $where, $operator = 'AND');

	public function first(callable $fn);
	
	public function column($column, $index_column = null);

	public function sort(callable $fn = null);

}
