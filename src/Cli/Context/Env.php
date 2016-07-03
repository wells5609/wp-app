<?php

namespace WordPress\Cli\Context;

class Env extends AbstractValues
{

	public function __construct(array $values = []) {
		parent::__construct(empty($values) ? $_ENV : $values);
	}

	public function offsetGet($key) {
		if (array_key_exists($key, $this->values)) {
			return $this->values[$key];
		}
		$value = getenv($key);
		return $value !== false ? $value : null;
	}

	public function offsetExists($key) {
		return array_key_exists($key, $this->values) || getenv($key) !== false;
	}

	public function offsetSet($key, $value) {
		$this->values[$key] = $value;
		putenv($key.'='.$value);
	}

	public function offsetUnset($key) {
		unset($this->values[$key]);
		putenv($key);
	}

	public function get($key, $default = null) {
		$value = $this->offsetGet($key);
		return $value !== null ? $value : $default;
	}

}
