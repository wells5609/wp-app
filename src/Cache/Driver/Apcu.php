<?php

namespace WordPress\Cache\Driver;

use WordPress\Cache\Cache;

class Apcu extends AbstractDriver
{

	public function exists($id) {
		return apcu_exists($this->prefix.$id);
	}

	public function get($id) {
		return apcu_fetch($this->prefix.$id);
	}

	public function set($id, $value, $ttl = Cache::DEFAULT_TTL) {
		return apcu_store($this->prefix.$id, $value, $ttl);
	}

	public function delete($id) {
		return apcu_delete($this->prefix.$id);
	}

	public function incr($id, $value = 1, $ttl = Cache::DEFAULT_TTL) {
		return apcu_inc($this->prefix.$id, $value);
	}

	public function decr($id, $value = 1, $ttl = Cache::DEFAULT_TTL) {
		return apcu_dec($this->prefix.$id, $value);
	}

	public function flush() {
		return apcu_delete($this->prefix);
	}

}
