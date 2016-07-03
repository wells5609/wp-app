<?php

namespace WordPress\Common;

use Countable;
use IteratorAggregate;
use ArrayIterator;
use ArrayObject;

class struct extends \ArrayObject implements \JsonSerializable
{
	
	public function __construct($data = [], $flags = 0, $iterator_class = 'ArrayIterator') {
		parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS, $iterator_class);
	}
	
	public function isEmpty() {
		return $this->count() === 0;
	}

	public function toArray() {
		return iterator_to_array($this);
	}
	
	public function contains($value) {
		return in_array($value, $this->getArrayCopy(), true);
	}
	
	public function jsonSerialize() {
		return $this->getArrayCopy();
	}
		
}

class Structure implements Countable, IteratorAggregate
{
		
	/**
	 * @var array
	 */
	protected $properties;
	
	public function __construct(array $data = []) {
		$this->properties = $data;
	}
	
	public function count() {
		return count($this->properties);
	}
	
	public function isEmpty() {
		return empty($this->properties);
	}
	
	public function contains($value) {
		return in_array($value, $this->properties, true);
	}
	
	public function toArray() {
		return $this->properties;
	}
	
	public function getIterator() {
		return new ArrayIterator($this->properties);
	}
	
}

trait PropertyAccessControlTrait 
{
	
	/**
	 * @var \ArrayObject
	 */
	private $readPropertyControl;
	
	/**
	 * @var \ArrayObject
	 */
	private $writePropertyControl;
	
	/**
	 * @var \ArrayObject
	 */
	private $deletePropertyControl;
	
	protected function initPropertyAccessControl() {
		$this->readPropertyControl = new ArrayObject([], ArrayObject::STD_PROP_LIST);
		$this->writePropertyControl = new ArrayObject([], ArrayObject::STD_PROP_LIST);
		$this->deletePropertyControl = new ArrayObject([], ArrayObject::STD_PROP_LIST);
	}
	
	public function isPropertyReadable($property) {
		return $this->readPropertyControl->offsetGet($property);
	}
	
	public function isPropertyWritable($property) {
		return $this->writePropertyControl->offsetGet($property);
	}
	
	public function isPropertyDeletable($property) {
		return $this->deletePropertyControl->offsetGet($property);
	}
	
	protected function setPropertyAccess($property, $read = null, $write = null, $delete = null) {
		if (isset($read)) {
			$this->readPropertyControl->offsetSet($property, (bool)$read);
		}
		if (isset($write)) {
			$this->writePropertyControl->offsetSet($property, (bool)$write);
		}
		if (isset($delete)) {
			$this->deletePropertyControl->offsetSet($property, (bool)$delete);
		}
	}
	
	protected function setPropertyReadable($property) {
		$this->readPropertyControl->offsetSet($property, true);
	}
	
	protected function setPropertyWritable($property) {
		$this->writePropertyControl->offsetSet($property, true);
	}
	
	protected function setPropertyDeletable($property) {
		$this->deletePropertyControl->offsetSet($property, true);
	}
	
	protected function setReadableProperties(array $properties) {
		$this->readPropertyControl->exchangeArray(array_fill_keys($properties, true));
	}
	
	protected function setWritableProperties(array $properties) {
		$this->writePropertyControl->exchangeArray(array_fill_keys($properties, true));
	}
	
	protected function setDeletableProperties(array $properties) {
		$this->deletePropertyControl->exchangeArray(array_fill_keys($properties, true));
	}
	
}
