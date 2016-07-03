<?php

namespace WordPress\Cli\Context;

class Server extends ReadOnlyValues
{

	public function __construct(array $values = []) {
		parent::__construct(empty($values) ? $_SERVER : $values);
	}

}
