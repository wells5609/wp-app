<?php

namespace WordPress\Cli\Io;

trait StdErrTrait
{
	
	/**
	 * @var resource
	 */
	protected $stdErr = STDERR;
	
	/**
	 * Returns the stream resource handle.
	 * 
	 * @return resource
	 */
	public function getStdErr() {
		return $this->stdErr;
	}
	
	/**
	 * Sets the underlying stream resource handle.
	 * 
	 * @param resource $handle
	 * 
	 * @throws \InvalidArgumentException if $handle is not a resource
	 */
	public function setStdErr($handle) {
		if (! is_resource($handle)) {
			throw new \InvalidArgumentException("Expecting stream handle, given: ".gettype($handle));
		}
		$this->stdErr = $handle;
	}
	
	/**
	 * Prints to stderr stream. The message and parameters are passed
	 * through `sprintf` before output.
	 *
	 * @param string  $msg The message to output in `printf` format.
	 * @param boolean $nl  Whether to output a new line after the message.
	 * 
	 * @return void
	 */
	public function error($msg, $nl = true) {
		fwrite($this->getStdErr(), $msg.($nl ? "\n" : ''));
	}
	
	/**
	 * Alias of error()
	 */
	public function err($msg, $nl = true) {
		return $this->error($msg, $nl);
	}
	
}
