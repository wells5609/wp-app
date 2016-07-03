<?php

namespace WordPress\Cli\Io;

trait StdInTrait
{
	
	/**
	 * @var resource
	 */
	protected $stdIn = STDIN;
	
	/**
	 * Returns the stream resource handle.
	 * 
	 * @return resource
	 */
	public function getStdIn() {
		return $this->stdIn;
	}
	
	/**
	 * Sets the underlying stream resource handle.
	 * 
	 * @param resource $handle
	 * 
	 * @throws \InvalidArgumentException if $handle is not a resource
	 */
	public function setStdIn($handle) {
		if (! is_resource($handle)) {
			throw new \InvalidArgumentException("Expecting stream handle, given: ".gettype($handle));
		}
		$this->stdIn = $handle;
	}
	
	/**
	 * Takes input from `STDIN` in the given format. If an end of transmission
	 * character is sent (^D), an exception is thrown.
	 *
	 * @param string  $format  A valid input format. See `fscanf` for documentation.
	 *                         If none is given, all input up to the first newline
	 *                         is accepted.
	 * @param boolean $hide    If true will hide what the user types in.
	 * 
	 * @throws \Exception  Thrown if ctrl-D (EOT) is sent as input.
	 * 
	 * @return string  The input with whitespace trimmed.
	 */
	public function read($format = null) {
		
		if ($format) {
			fscanf($this->getStdIn(), $format."\n", $line);
		} else {
			$line = fgets($this->getStdIn());
		}
		
		if ($line === false) {
			throw new \Exception('Caught ^D during input');
		}
		
		return trim($line);
	}
	
	/**
	 * Alias of ReadableTrait::read()
	 */
	public function input($format = null) {
		return $this->read($format);
	}
	
}
