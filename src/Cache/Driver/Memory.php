<?php

namespace WordPress\Cache\Driver;

class Memory extends AbstractDriver
{
	
	protected $cache = array();

	public function getPrefix() {
		return $this->prefix;
	}

	public function exists($id) {
		return isset($this->cache[$id]);
	}

	public function get($id) {
		return isset($this->cache[$id]) ? $this->cache[$id] : null;
	}

	public function set($id, $value, $ttl = null) {
		$this->cache[$id] = $value;
	}

	public function delete($id) {
		unset($this->cache[$id]);
	}

	public function incr($id, $value = 1, $ttl = null) {
		if (! isset($this->cache[$id])) {
			$this->cache[$id] = 0;
		}
		$this->cache[$id] += $value;
	}

	public function decr($id, $value = 1, $ttl = null) {
		if (! isset($this->cache[$id])) {
			$this->cache[$id] = 0;
		}
		$this->cache[$id] -= $value;
	}

	public function flush() {
		$this->cache = array();
	}

}
