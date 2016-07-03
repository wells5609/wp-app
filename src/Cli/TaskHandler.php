<?php

namespace WordPress\Cli;

abstract class TaskHandler implements TaskHandlerInterface
{
	
	/**
	 * @var \WordPress\Cli\Request
	 */
	protected $request;
	
	/**
	 * @var \WordPress\Cli\StdIo
	 */
	protected $io;
	
	/**
	 * Set the request object.
	 * 
	 * @param \WordPress\Cli\Request
	 */
	public function setRequest(Request $request) {
		$this->request = $request;
	}
	
	/**
	 * Returns the request object.
	 * 
	 * @return \WordPress\Cli\Request
	 */
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * Sets the StdIo instance.
	 * 
	 * @param \WordPress\Cli\StdIo $io
	 */
	public function setIo(StdIo $io) {
		$this->io = $io;
	}
	
	/**
	 * Returns the StdIo instance.
	 * 
	 * @return \WordPress\Cli\StdIo
	 */
	public function getIo() {
		return $this->io;
	}
	
	/**
	 * Returns the name of the task handled.
	 * 
	 * @return string
	 */
	public function getTaskName() {
		return basename(str_replace(array('\\', 'handler'), array('/', ''), strtolower(get_class($this))));
	}
	
}
