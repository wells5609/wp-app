<?php

namespace WordPress\Application;

use WordPress\Event;
use WordPress\App;

class Action extends Event
{

	const PRELOAD = 'preload';
	const LOAD = 'load';
	const INIT = 'init';
	const LOADED = 'loaded';
	const REQUEST = 'request';
	const READY = 'ready';
	
	public function bind($event, $priority = 10) {
		add_action($event, array($this, 'execute'), $priority);
		return $this;
	}

	public function execute() {
		do_action($this->getName(), App::instance());
	}

}
