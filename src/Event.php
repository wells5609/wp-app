<?php

namespace WordPress;

use RuntimeException;

class Event
{
	
	private $name;
	private $preventDefault = false;
	private $stopPropagation = false;
	private static $instances = [];
	
	/**
	 * Returns an event by name.
	 * 
	 * @param string $tag
	 * 
	 * @return \WordPress\Event
	 */
	public static function get($tag) {
		if (! isset(self::$instances[$tag])) {
			self::$instances[$tag] = new self($tag);
		}
		return self::$instances[$tag];
	}
	
	/**
	 * Returns the current event.
	 * 
	 * @throws \RuntimeException if no event is running
	 * 
	 * @return \WordPress\Event
	 */
	public static function current() {
		if (empty($GLOBALS['wp_current_filter'])) {
			throw new RuntimeException("No current filter or action");
		}
		return self::get(end($GLOBALS['wp_current_filter']));
	}
	
	/**
	 * Constructor. Accepts the unique event name.
	 * 
	 * @param string $tag
	 * 
	 * @throws \RuntimeException if event already exists
	 */
	public function __construct($tag) {
		if (isset(self::$instances[$tag])) {
			throw new RuntimeException("Cannot create '$tag': event already exists.");
		}
		$this->name = $tag;
		self::$instances[$tag] = $this;
	}

	/**
	 * Magic protected property access.
	 * 
	 * Allows access to $name, $preventDefault, and $stopPropagation
	 *
	 * @param string $key
	 *
	 * @throws \OutOfBoundsException if an invalid property is requested
	 *
	 * @return mixed
	 */
	public function __get($key) {
		if (isset($this->$key)) {
			return $this->$key;
		}
		throw new \OutOfBoundsException("Invalid property '$key'");
	}
	
	/**
	 * Magic protected property access.
	 * 
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public function __isset($key) {
		return isset($this->$key);
	}
	
	/**
	 * Returns the event name.
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns the event's callbacks from $GLOBALS['wp_filter']
	 * 
	 * @return array
	 */
	public function getCallbacks() {
		if ($this->exists()) {
			return $GLOBALS['wp_filter'][$this->name];
		}
	}
	
	/**
	 * Checks whether this event has any callbacks associated with it.
	 * 
	 * @return boolean
	 */
	public function exists() {
		return isset($GLOBALS['wp_filter'][$this->name]);
	}
	
	/**
	 * Checks whether the event is currently running (excluding parents).
	 */
	public function isCurrent() {
		if (empty($GLOBALS['wp_current_filter'])) {
			return false;
		}
		return $this->name === end($GLOBALS['wp_current_filter']);
	}
	
	/**
	 * Checks whether the event is currently running (including parents).
	 * 
	 * @return boolean
	 */
	public function isActive() {
		if (empty($GLOBALS['wp_current_filter'])) {
			return false;
		}
		return in_array($this->name, $GLOBALS['wp_current_filter'], true);
	}
	
	/**
	 * Adds a callback to the event.
	 * 
	 * @param callable $callback
	 * @param number $priority
	 * @param number $args
	 * 
	 * @throws RuntimeException if propagation of the event has stopped
	 * 
	 * @return \WordPress\Event
	 */
	public function attach(callable $callback, $priority = 10, $args = 1) {
		if ($this->stopPropagation) {
			throw new RuntimeException("Invalid operation: Propagation is stopped.");
		}
		add_filter($this->name, $callback, $priority, $args);
		return $this;
	}
	
	/**
	 * Sets the event to prevent its default.
	 * 
	 * @return \WordPress\Event
	 */
	public function preventDefault() {
		$this->preventDefault = true;
		return $this;
	}
	
	/**
	 * Checks whether the event's default is prevented.
	 * 
	 * @return boolean
	 */
	public function isDefaultPrevented() {
		return $this->preventDefault;
	}
	
	/**
	 * Stops further propagation of the event.
	 * 
	 * @return \WordPress\Event
	 */
	public function stopPropagation() {
		$this->stopPropagation = true;
		$name = $this->name;
		//$callbacks = $this->getCallbacks();
		remove_all_filters($this->name);
		//add_action($this->name, function() use($name, $callbacks) {
		//	$GLOBALS['wp_filter'][$name] = $callbacks;
		//}, PHP_INT_MAX);
		return $this;
	}
	
	/**
	 * Checks whether propagation of the event has been stopped.
	 * 
	 * @return boolean
	 */
	public function isPropagationStopped() {
		return $this->stopPropagation;
	}
	
	/**
	 * Calls the event with the given array of arguments.
	 * 
	 * @param array $args
	 * 
	 * @throws RuntimeException if propagation is stopped
	 * 
	 * @return mixed Result of filter, if any
	 */
	public function call(array $args = array()) {
		if ($this->stopPropagation) {
			throw new RuntimeException("Cannot call event '$this->name': Propagation is stopped.");
		}
		return apply_filters_ref_array($this->name, $args);
	}

	/**
	 * Calls the event with any given function arguments.
	 *
	 * @param array $args
	 *
	 * @throws RuntimeException if propagation is stopped
	 *
	 * @return mixed Result of filter, if any
	 */
	public function __invoke(/**[...]*/) {
		return $this->call(func_get_args());
	}

	/**
	 * Build Unique ID for storage and retrieval.
	 *
	 * @param string   $tag      Used in counting how many hooks were applied
	 * @param callable $callback Used for creating unique id
	 * @param int|bool $priority Used in counting how many hooks were applied. If === false
	 *                           and $function is an object reference, we return the unique
	 *                           id only if it already has one, false otherwise.
	 *
	 * @return string|false Unique ID for usage as array key or false if $priority === false
	 *                      and $function is an object reference, and it does not already have
	 *                      a unique id.
	 */
	protected function buildUniqueIdentifier($tag, $callback, $priority) {
	
		if (is_string($callback)) {
			return $callback;
		}
	
		if (is_object($callback)) {
			return spl_object_hash($callback);
		}
	
		$callback = (array)$callback;
	
		if (is_object($callback[0]) ) {
			return spl_object_hash($callback[0]).$callback[1];
		}
	
		return $callback[0].'::'.$callback[1];
	}
	
}
