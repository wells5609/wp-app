<?php

namespace WordPress\Data\Storage;

use WordPress\Database\Table;
use WordPress\Data\Factory;
use WordPress\Data\ModelInterface;
use WordPress\Data\FactoryInterface;
use WordPress\Data\StorageInterface;

class Database implements StorageInterface
{

	/**
	 * The database table.
	 *
	 * @var \WordPress\Database\Table
	 */
	protected $table;

	/**
	 * The model factory.
	 *
	 * @var \WordPress\Data\FactoryInterface
	 */
	protected $factory;

	/**
	 * Constructor.
	 *
	 * @param \WordPress\Database\Table $table
	 * @param \WordPress\Data\FactoryInterface $factory
	 */
	public function __construct(Table $table, FactoryInterface $factory) {
		$this->table = $table;
		$this->factory = $factory;
	}

	/** -----------------------------------------------
	 *  Implements StorageInterface
	 * --------------------------------------------- */

	/**
	 * Returns the table name.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->table->getName();
	}

	/**
	 * Returns the model factory.
	 *
	 * @return \WordPress\Data\FactoryInterface
	 */
	public function getFactory() {
		return $this->factory;
	}
	
	/**
	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public function find($args) {
		$result = $this->table->find($this->getArrayArgs($args));
		if (empty($result)) {
			return array();
		}
		return array_map(array($this->factory, 'create'), $result);
	}

	/**
	 * Retrieves a single record from storage.
	 *
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public function findOne($args) {
		$result = $this->table->findOne($this->getArrayArgs($args));
		return empty($result) ? null : $this->factory->create($result);
	}

	/**
	 * Delete a row in the table.
	 *
	 * @see wpdb::delete()
	 */
	public function delete(ModelInterface $model) {
		return $this->table->delete($this->getArrayArgs($model));
	}

	/**
	 * Saves a row in the table.
	 *
	 * @see wpdb::update()
	 */
	public function save(ModelInterface $model) {
		$dataArray = $this->getArrayArgs($data);
		$pkField = $this->table->getPrimaryKeyColumn()->name;
		$pk = $model->$pkField;
		if (! $pk) {
			return $this->table->insert($dataArray);
		}
		return $this->table->update($dataArray, array($pkField => $pk));
	}

	/**
	 * Returns the arguments as an array.
	 *
	 * If a non-array argument is passed, an array will be created using the table's
	 * primary column name as the key with the given argument as the (only) value.
	 *
	 * @param mixed $args
	 *
	 * @return array
	 */
	protected function getArrayArgs($args) {
		if ($args instanceof ModelInterface) {
			return $args->getModelData();
		}
		if (is_array($args)) {
			return $args;
		}
		return array($this->table->getPrimaryKeyColumn()->name => $args);
	}

}
