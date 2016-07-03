<?php

namespace WordPress\Data;

interface FactoryInterface
{

	/**
	 * Returns the associated Type object.
	 * 
	 * @return \WordPress\Data\Type
	 */
	public function getType();

	/**
	 * Creates a new model.
	 *
	 * @param mixed $data [Optional]
	 *
	 * @return \WordPress\Data\ModelInterface
	 */
	public function create($data = null);

	/**
	 * Creates an array of models.
	 *
	 * @param array $data
	 *
	 * @return \WordPress\Data\ModelInterface[]
	 */
	public function createArray(array $data);
	
}