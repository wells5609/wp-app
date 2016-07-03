<?php

namespace WordPress\Cache\Driver;

use WordPress\Cache\Cache;

class Apc extends AbstractDriver
{

	public function exists($id) {
		return apc_exists($this->prefix.$id);
	}

	public function get($id) {
		return apc_fetch($this->prefix.$id);
	}

	public function set($id, $value, $ttl = Cache::DEFAULT_TTL) {
		return apc_store($this->prefix.$id, $value, $ttl);
	}

	public function delete($id) {
		return apc_delete($this->prefix.$id);
	}

	public function incr($id, $value = 1, $ttl = Cache::DEFAULT_TTL) {
		return apc_inc($this->prefix.$id, $value);
	}

	public function decr($id, $value = 1, $ttl = Cache::DEFAULT_TTL) {
		return apc_dec($this->prefix.$id, $value);
	}

	public function flush() {
		return apc_delete($this->prefix);
	}

}
