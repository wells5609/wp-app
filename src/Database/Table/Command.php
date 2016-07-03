<?php

namespace WordPress\Database\Table;

use WordPress\Database\Table;

abstract class Command
{
	
	/**
	 * @var \WordPress\Database\Table
	 */
	protected $table;
	
	public function __construct(Table $table) {
		$this->table = $table;
	}
	
	abstract public function __invoke();
	
}
