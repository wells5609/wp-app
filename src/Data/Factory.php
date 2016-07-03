<?php

namespace WordPress\Data;

class Factory implements FactoryInterface
{

	protected $type;

	/**
	 * Constructor.
	 *
	 * @param string $modelClass [Optional] Default = 'WordPress\Data\Model'
	 */
	public function __construct(Type $type) {
		$this->type = $type;
	}

	/**
	 * Returns the associated Type object.
	 *
	 * @return \WordPress\Data\Type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Creates a new model.
	 *
	 * @param mixed $data [Optional]
	 *
	 * @return \WordPress\Data\ModelInterface
	 */
	public function create($data = null) {
		$class = $this->type->getModelClassname();
		$object = new $class($data);
		$object->setModelStorage($this->type->getStorage());
		return $object;
	}
	
	/**
	 * Creates an array of models.
	 * 
	 * @param array $data
	 * 
	 * @return \WordPress\Data\ModelInterface[]
	 */
	public function createArray(array $data) {
		return array_map(array($this, 'create'), $data);
	}
	
}
