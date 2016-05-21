<?php

namespace WordPress\Database\Table;

use InvalidArgumentException;

/**
 * Represents a database table command.
 */
abstract class Command
{
	
	/**
	 * Schema for the table on which to operate.
	 * 
	 * @var \WordPress\Database\Table\Schema
	 */
	protected $schema;
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Database\Table\Schema $schema
	 * 
	 * @throws \RuntimeException if schema is invalid
	 */
	public function __construct(Schema $schema) {
		if (! $schema->validate()) {
			throw new InvalidArgumentException('Invalid table schema');
		}
		$this->schema = $schema;
	}
	
	/**
	 * Returns the table schema.
	 * 
	 * @return \WordPress\Database\Table\Schema
	 */
	public function getSchema() {
		return $this->schema;
	}
	
	/**
	 * Returns boolean true if command succeeded, false if fail.
	 * 
	 * @return boolean
	 */
	abstract public function success();
	
	/**
	 * Executes the command.
	 * 
	 * @return void
	 */
	abstract public function __invoke();
	
}
