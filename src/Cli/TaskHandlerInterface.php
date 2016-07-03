<?php

namespace WordPress\Cli;

interface TaskHandlerInterface
{
	
	/**
	 * Set the request object.
	 * 
	 * @param \WordPress\Cli\Request
	 */
	public function setRequest(Request $request);
	
	/**
	 * Returns the request object.
	 * 
	 * @return \WordPress\Cli\Request
	 */
	public function getRequest();
	
	/**
	 * Sets the StdIo instance.
	 * 
	 * @param \WordPress\Cli\StdIo $io
	 */
	public function setIo(StdIo $io);
	
	/**
	 * Returns the StdIo instance.
	 * 
	 * @return \WordPress\Cli\StdIo
	 */
	public function getIo();
		
	/**
	 * Returns the name of the task handled.
	 * 
	 * @return string
	 */
	public function getTaskName();
	
	/**
	 * Handles the default action.
	 */
	public function __invoke();
	
}
