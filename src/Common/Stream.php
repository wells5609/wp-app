<?php

namespace WordPress\Common;

/**
 * Stream provides an object wrapper for a stream.
 *
 * @author wells
 *
 * @since 1.0
 *
 * @version 1.0
 */
class Stream
{

	/**
	 * The open stream handle
	 * @var resource
	 */
	protected $handle;
	
	/**
	 * The stream's mode.
	 * @var string
	 */
	protected $mode;
	
    /**
     * Creates a new file stream from path.
     *
     * @param string path The file path
     * @param string mode [Optional] fopen() mode. Default is "rb"
     * @return \WordPress\Common\Stream
     */
    public static function fromFile($path, $mode = 'rb') {
        return new static(fopen($path, $mode));
    }

    /**
     * Returns a temporary PHP stream using 2 MB of memory then a file
     *
     * @param  string mode [Optional] Default is "w+b"
     * @return \WordPress\Common\Stream
     */
    public static function temp($mode = 'w+b') {
        return new static(fopen('php://temp/maxmemory=2097152', $mode));
    }

	/**
	 * Constructor. Sets the stream handle and mode.
	 *
	 * @param resource handle
	 * @param string mode
	 */
	public function __construct($handle) {
		
		if (! is_resource($handle)) {
            throw new \InvalidArgumentException("Expecting resource, given: '".gettype($handle)."'");
        }

        $this->handle = $handle;
		$this->mode = $this->getMeta('mode');
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

        return fclose($this->handle);
	}

	/**
	 * Separates any underlying resources from the stream.
	 *
	 * @return void
	 */
	public function detach() {
		$this->close();
        $this->handle = null;
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

        if (strpos($this->mode, '+') !== false) {
            return true;
        }
		
        return $this->mode[0] === 'r';
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
		
		if (! $this->isReadable()) {
			return false;
		}

		return fread($this->handle, $length);
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
        
        if (strpos($this->mode, "+") !== false) {
            return true;
        }

        return in_array($this->mode[0], ["w", "a", "x", "c"], true);
    }

	/**
	 * Write data to the stream.
	 *
	 * @param string str The string that is to be written.
	 *
	 * @return int|bool Returns the number of bytes written to the stream on
	 *                  success or FALSE on failure.
	 */
	public function write($str) {
		
		if (! $this->isWritable()) {
            return false;
		}

		return fwrite($this->handle, $str);
	}

	/**
	 * Returns whether or not the stream is seekable
	 *
	 * @return bool
	 */
	public function isSeekable() {
        return $this->getMeta('seekable');
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
		
		if ($this->isSeekable()) {
			rewind($this->handle);
		}

		return $this->getContents();
	}

}
