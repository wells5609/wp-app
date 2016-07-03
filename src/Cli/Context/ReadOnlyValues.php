<?php

namespace WordPress\Cli\Context;

abstract class ReadOnlyValues extends AbstractValues
{

	public function offsetSet($key, $value) {
		throw new \RuntimeException("Cannot set value: container is read-only.");
	}

	public function offsetUnset($key) {
		throw new \RuntimeException("Cannot unset value: container is read-only.");
	}

}
