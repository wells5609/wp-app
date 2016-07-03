<?php

namespace WordPress\Database\Table\Command;

use WordPress\Database\Table\Command;

class Drop extends Command
{
	
	public function __invoke() {
		$name = $this->table->getTableName();
		if (! $this->table->isInstalled()) {
			return true;
		}
		$connection = $this->table->getConnection();
		$connection->query("DROP TABLE $name");
		return ! in_array($name, $connection->getTableNames(true));
	}
	
}
