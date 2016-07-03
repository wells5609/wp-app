<?php

namespace WordPress\Cli;

class StdIo
{
	
	use Io\StdInTrait;
	use Io\StdOutTrait;
	use Io\StdErrTrait {
		write as error;
		out as err;
	}
	
	/**
	 * Displays an input prompt. If no default value is provided the prompt will
	 * continue displaying until input is received.
	 *
	 * @param string      $question The message to display to the user.
	 * @param bool|string $default  A default value if the user provides no input.
	 * @param string      $marker   A string to append to the message and default value.
	 * @return string  The users input.
	 */
	public function prompt($msg, $default = false, $marker = ': ') {
			
		if ($default && strpos($msg, '[') === false) {
			$msg .= ' ['.$default.']';
		}
		
		while (true) {
		
			$this->write($msg.$marker, false);
		
			$line = $this->read(null);
		
			if (! empty($line)) {
				return $line;
			}
		
			if ($default !== false) {
				return $default;
			}
		}
	}
	
}

