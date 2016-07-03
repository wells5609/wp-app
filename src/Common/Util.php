<?php

namespace WordPress\Common;

class Util
{

	public static function iterate($iterator) {
		if ($iterator instanceof \Traversable || is_array($iterator)) {
			return $iterator;
		}
		return is_object($iterator) ? get_object_vars($iterator) : (array)$iterator;
	}

}
