<?php

namespace WordPress\Cache\Driver;

use WordPress\Cache\Cache;

class XCache extends AbstractDriver
{

	public function exists($id) {
		return xcache_isset($this->prefix.$id);
	}

	public function get($id) {
		return xcache_get($this->prefix.$id);
	}

	public function set($id, $value, $ttl = Cache::DEFAULT_TTL) {
		return xcache_set($this->prefix.$id, $value, $ttl);
	}

	public function delete($id) {
		return xcache_unset($this->prefix.$id);
	}

	public function incr($id, $value = 1, $ttl = Cache::DEFAULT_TTL) {
		return xcache_inc($this->prefix.$id, $value, $ttl);
	}

	public function decr($id, $value = 1, $ttl = Cache::DEFAULT_TTL) {
		return xcache_dec($this->prefix.$id, $value, $ttl);
	}

	public function flush() {
		return xcache_unset_by_prefix($this->prefix);
	}

}
