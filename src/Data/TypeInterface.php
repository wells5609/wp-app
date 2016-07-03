<?php

namespace WordPress\Data;

interface TypeInterface
{

	/**
	 * Returns the name of the data type.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns a description of the data type.
	 *
	 * @return string
	 */
	public function getDescription();

	/**
	 * Returns the name of the PHP class for models of this type.
	 *
	 * @return string
	 */
	public function getModelClassname();

	/**
	 * Returns the associated storage container.
	 *
	 * @return \WordPress\Data\StorageInterface
	 */
	public function getStorage();

}
