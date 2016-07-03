<?php

namespace WordPress\Cli\Io;

use InvalidArgumentException;
use RuntimeException;

class Stream
{
	
	/**
	 * The stream handle.
	 * 
	 * @var resource
	 */
	protected $handle;
	
	/**
	 * Whether the stream is readable.
	 * 
	 * @var boolean
	 */
	protected $readable = false;
	
	/**
	 * Whether the stream is writable.
	 *
	 * @var boolean
	 */
	protected $writable = false;

	/**
	 * Creates a STDIN stream.
	 *
	 * @param resource $handle [Optional] The STDIN resource handle
	 * 
	 * @return \WordPress\Cli\Io\Stream
	 */
	public static function stdin($handle = null) {
		return new static($handle ?: fopen(STDIN, 'rb'));
	}

	/**
	 * Creates a STDOUT stream.
	 *
	 * @param resource $handle [Optional] The STDOUT resource handle
	 *
	 * @return \WordPress\Cli\Io\Stream
	 */
	public static function stdout($handle = null) {
		return new static($handle ?: fopen(STDOUT, 'wb'));
	}

	/**
	 * Creates a STDERR stream.
	 *
	 * @param resource $handle [Optional] The STDERR resource handle
	 *
	 * @return \WordPress\Cli\Io\Stream
	 */
	public static function stdout($handle = null) {
		return new static($handle ?: fopen(STDERR, 'w+b'));
	}
	
	/**
	 * Constructor. Sets the stream handle.
	 *
	 * @param resource handle
	 */
	public function __construct($handle) {
		if (! is_resource($handle) || get_resource_type($handle) !== 'stream') {
			throw new InvalidArgumentException("Invalid stream handle");
		}
		$this->handle = $handle;
		$mode = $this->getMeta('mode');
		if (strpos($mode, '+') !== false) {
			$this->readable = true;
			$this->writable = true;
		} else {
			if (strpos($mode, 'r') !== false) {
				$this->readable = true;
			}
			if (strcspn($mode, 'wxca') !== strlen($mode)) {
				$this->writable = true;
			}
		}
	}

	/**
	 * Read data from the stream
	 *
	 * @param int length Read up to length bytes from the object and return
	 *                    them. Fewer than length bytes may be returned if
	 *                    underlying stream call returns fewer bytes.
	 *
	 * @return string     Returns the data read from the stream.
	 */
	public function read($length) {
		if (! $this->readable) {
			throw new RuntimeException("Stream is not readable");
		}
		return fread($this->handle, $length);
	}

	/**
	 * Write data to the stream.
	 *
	 * @param string str The string that is to be written.
	 *
	 * @return int|bool Returns the number of bytes written to the stream on
	 *                  success or FALSE on failure.
	 */
	public function write($string) {
		if (! $this->writable) {
			throw new RuntimeException("Stream is not writable");
		}
		return fwrite($this->handle, $string);
	}

	/**
	 * Reads a line of data from the stream
	 *
	 * @return string     Returns the data read from the stream.
	 */
	public function readLine() {
		if (! $this->readable) {
			throw new RuntimeException("Stream is not readable");
		}
		return fgets($this->handle);
	}

	/**
	 * Write data to the stream and appends a new line.
	 *
	 * @param string str The string that is to be written.
	 *
	 * @return int|bool Returns the number of bytes written to the stream on
	 *                  success or FALSE on failure.
	 */
	public function writeLine($string) {
		return $this->write($string.PHP_EOL);
	}

	/**
	 * Closes the stream and any underlying resources.
	 *
	 * @return boolean True if the stream was (or is already) closed, or false if it cannot be closed.
	 */
	public function close() {
		if (! isset($this->handle)) {
			return true;
		}
		$handle = $this->detach();
		return fclose($handle);
	}
	
	/**
	 * Separates any underlying resources from the stream.
	 *
	 * @return resource
	 */
	public function detach() {
		$handle = $this->handle;
		$this->handle = null;
		return $handle;
	}
	
	/**
	 * Returns the current position of the file read/write pointer
	 *
	 * @return int|bool Position of the file pointer or false on error
	 */
	public function tell() {
		return ftell($this->handle);
	}
	
	/**
	 * Returns true if the stream is at the end of the stream.
	 *
	 * @return bool
	 */
	public function eof() {
		return feof($this->handle);
	}

	/**
	 * Returns whether or not the stream is readable.
	 *
	 * Stream is readable if the mode starts with "r" or includes "+".
	 *
	 * @return bool
	 */
	public function isReadable() {
		return $this->readable;
	}
	
	/**
	 * Returns whether or not the stream is writable
	 *
	 * Stream is writable if the mode includes "+" or if the first character
	 * is one of "w", "a", "x" or "c".
	 *
	 * @return bool
	 */
	public function isWritable() {
		return $this->writable;
	}

	/**
	 * Returns whether or not the stream is seekable
	 *
	 * @return bool
	 */
	public function isSeekable() {
		return (bool)$this->getMeta('seekable');
	}
	
	/**
	 * Seek to a position in the stream
	 *
	 * @link   http://www.php.net/manual/en/function.fseek.php
	 *
	 * @param int offset Stream offset
	 * @param int whence Specifies how the cursor position will be calculated
	 *                    based on the seek offset. Valid values are identical
	 *                    to the built-in PHP whence values for `fseek()`.
	 *                    SEEK_SET: Set position equal to offset bytes (default).
	 *                    SEEK_CUR: Set position to current location plus offset.
	 *                    SEEK_END: Set position to end-of-stream plus offset.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure
	 */
	public function seek($offset, $whence = SEEK_SET) {
		if (! $this->isSeekable()) {
			return false;
		}
		return fseek($this->handle, $offset, $whence);
	}
	
	/**
	 * Returns the remaining contents in a string, up to maxlength bytes.
	 *
	 * @param int maxLength The maximum bytes to read. Defaults to -1 (read
	 *                       all the remaining buffer).
	 * @return string
	 */
	public function getContents($maxLength = -1) {
		return stream_get_contents($this->handle, $maxLength);
	}
	
	/**
	 * Returns metadata for the stream
	 *
	 * @return array
	 */
	public function getMetaData() {
		return stream_get_meta_data($this->handle);
	}
	
	/**
	 * Returns a meta data item
	 *
	 * @param string key Item from metadata to retrieve
	 *
	 * @return mixed
	 */
	public function getMeta($key) {
		$meta = $this->getMetaData();
		return isset($meta[$key]) ? $meta[$key] : null;
	}
	
	/**
	 * Get the size of the stream if known
	 *
	 * @return int|bool Returns the size in bytes if known, or false if unknown
	 */
	public function getSize() {
		$stat = fstat($this->handle);
		return isset($stat['size']) ? $stat['size'] : false;
	}
	
	/**
	 * Attempts to seek to the beginning of the stream and reads all data into
	 * a string until the end of the stream is reached.
	 *
	 * Warning: This could attempt to load a large amount of data into memory.
	 *
	 * @return string
	 */
	public function __toString() {
		try {
			if ($this->isSeekable()) {
				rewind($this->handle);
			}
			return $this->getContents();
		} catch (\Exception $e) {
			return '';
		}
	}
	
}