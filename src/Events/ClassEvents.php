<?php

namespace WordPress\Events;

use InvalidArgumentException;
use ReflectionClass;

class ClassEvents
{
	
	/**
	 * @var object
	 */
	protected $object;
	
	/**
	 * @var \ReflectionClass
	 */
	protected $reflection;
	
	public function __construct($object) {
		
		if (! is_object($object)) {
			throw new InvalidArgumentException('Expecting object, given: '.gettype($object));
		}
		
		$this->object = $object;
		$this->reflection = new ReflectionClass($this->object);
	}
	
	public function addAction($tag, $method, $priority = 1, $num_params = null) {
		
		if ($num_params === null) {
			$num_params = $this->reflection->getMethod($method)->getNumberOfParameters();
		}
		
		add_action($tag, [$this->object, $method], $priority, $num_args);
	}
	
	public function addFilter($tag, $method, $priority = 1, $num_params = null) {
		
		if ($num_params === null) {
			$num_params = $this->reflection->getMethod($method)->getNumberOfParameters();
		}
		
		add_filter($tag, [$this->object, $method], $priority, $num_params);
	}
	
}
