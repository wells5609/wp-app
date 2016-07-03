<?php

namespace WordPress\Cache;

class Cache
{
	
	const PREFIX = 'wpcache_';
	
	/**
	 * Default TTL (2 days)
	 * 
	 * @var int
	 */
	const DEFAULT_TTL = 172800;
	
	/**
	 * Cache driver.
	 * 
	 * @var \WordPress\Cache\Driver\DriverInterface
	 */
	protected $driver;
	
	/**
	 * Non-persistent groups.
	 * 
	 * @var array
	 */
	protected $nonPersistentGroups = array();
	
	public function start($driver = null) {
		if (! isset($this->driver)) {
			$this->setDriver($driver);
		}
		if (method_exists($this->driver, 'start')) {
			$this->driver->start();
		}
	}
	
	public function setDriver(Driver\DriverInterface $driver = null) {
		if (! isset($driver)) {
			$class = static::detectDriverClass();
			$driver = new $class();
		}
		$this->driver = $driver;
	}
	
	public function getDriver() {
		return $this->driver;
	}

	public function getPrefix() {
		return $this->driver->getPrefix();
	}

	public function exists($id, $group = null) {
		return $this->driver->exists($this->uid($id, $group));
	}

	public function get($id, $group = null) {
		return $this->driver->get($this->uid($id, $group));
	}

	public function set($id, $value, $group = null, $ttl = self::DEFAULT_TTL) {
		if (isset($group) && ! empty($this->nonPersistentGroups)) {
			if (in_array($group, $this->nonPersistentGroups, true)) {
				return;
			}
		}
		return $this->driver->set($this->uid($id, $group), $value, $ttl);
	}

	public function delete($id, $group = null) {
		return $this->driver->delete($this->uid($id, $group));
	}

	public function incr($id, $val = 1, $group = null, $ttl = self::DEFAULT_TTL) {
		return $this->driver->incr($this->uid($id, $group), $val, $ttl);
	}

	public function decr($id, $val = 1, $group = null, $ttl = self::DEFAULT_TTL) {
		return $this->driver->decr($this->uid($id, $group), $val, $ttl);
	}

	public function flush() {
		return $this->driver->flush();
	}
	
	public function addNonPersistentGroups(array $groups) {
		if (empty($this->nonPersistentGroups)) {
			$this->nonPersistentGroups = $groups;
		} else {
			$this->nonPersistentGroups = array_merge($this->nonPersistentGroups, $groups);
		}
	}
	
	public static function detectDriverClass() {
		
		if (function_exists('apcu_fetch')) {
			if (ini_get('apc.enabled') && (PHP_SAPI !== 'cli' || ini_get('apc.enable_cli'))) {
				return 'WordPress\Cache\Driver\Apcu';
			}
		}
		
		if (function_exists('xcache_get')) {
			return 'WordPress\Cache\Driver\XCache';
		}
		
		return 'WordPress\Cache\Driver\Memory';
		
		$map = array(
			'apcu_fetch' => 'WordPress\\Cache\\Driver\\Apcu',
			'apc_fetch' => 'WordPress\\Cache\\Driver\\Apc',
			'xcache_get' => 'WordPress\\Cache\\Driver\\XCache',
			'serialize' => 'WordPress\\Cache\\Driver\\Memory',
		);
		
		foreach($map as $func => $class) {
			if (function_exists($func)) {
				if ('apcu_fetch' === $func && ! apcu_enabled()) {
					continue;
				}
				$driverClass = $class;
				break;
			}
		}
		
		return new $driverClass();
	}
	
	protected function uid($id, $group = null) {
		return (empty($group) ? '' : $group.':').$id;
	}
	
}
