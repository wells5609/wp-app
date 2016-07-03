<?php

namespace WordPress\Cli\Io;

trait StdOutTrait
{
	
	/**
	 * @var resource
	 */
	protected $stdOut = STDOUT;
	
	/**
	 * Returns the stream resource handle.
	 * 
	 * @return resource
	 */
	public function getStdOut() {
		return $this->stdOut;
	}
	
	/**
	 * Sets the underlying stream resource handle.
	 * 
	 * @param resource $handle
	 * 
	 * @throws \InvalidArgumentException if $handle is not a resource
	 */
	public function setStdOut($handle) {
		if (! is_resource($handle)) {
			throw new \InvalidArgumentException("Expecting stream handle, given: ".gettype($handle));
		}
		$this->stdOut = $handle;
	}
	
	/**
	 * Shortcut for printing to `STDOUT`. The message and parameters are passed
	 * through `sprintf` before output.
	 *
	 * @param string  $msg The message to output in `printf` format.
	 * @param boolean $nl  Whether to output a new line after the message.
	 * 
	 * @return void
	 */
	public function write($msg, $nl = true) {
		fwrite($this->getStdOut(), $msg.($nl ? "\n" : ''));
	}
	
	/**
	 * Alias of WritableTrait::write()
	 */
	public function out($msg, $nl = true) {
		return $this->write($msg, $nl);
	}
	
	/**
	 * Prints a horizontal separator of dashes.
	 * 
	 * @param int $length [Optional] Default = 60
	 * @param boolean $nl [Optional] Default = true
	 * 
	 * @return void
	 */
	public function hr($length = 60, $nl = true) {
		$this->write(str_repeat('-', $length), $nl);
	}
	
	/**
	 * Prints an array of items recursively as an unordered list.
	 * 
	 * @param array $item
	 * 
	 * @return void
	 */
	public function listItems(array $items) {
			
		foreach($items as $item => $string) {
				
			if (is_numeric($item)) {
				$this->write("- $string");
			} else {
					
				$this->write("- $item");
				
				if (is_array($string)) {
					$this->writeArray($string);
				} else {
					$this->write("   $string");
				}
			}
		}
	}
	
	protected function writeArray(array $array, $level = 1) {
		foreach($array as $string) {
			if (is_array($string)) {
				$this->writeArray($string, $level + 1);
			} else {
				$this->write(str_repeat(' ', $level * 2)."- $string");
			}
		}
	}
	
}
