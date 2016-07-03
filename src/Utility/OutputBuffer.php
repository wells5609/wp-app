<?php

namespace WordPress\Utility;

class OutputBuffer
{

	private $valid;
	private $content;

	public function __construct(callable $callback = null) {
		$this->valid = ob_start($callback);
	}

	public function isValid() {
		return $this->valid;
	}

	public function getClean() {
		if (! isset($this->content)) {
			if ($this->valid) {
				if ($this->content = ob_get_clean()) {
					$this->valid = false;
				}
			}
		}
		return $this->content;
	}

	public function getContents() {
		if (isset($this->content)) {
			return $this->content;
		}
		if ($this->valid) {
			return ob_get_contents();
		}
		return false;
	}
	
	public function getLength() {
		if (isset($this->content)) {
			return strlen($this->content);
		}
		if ($this->valid) {
			return ob_get_length();
		}
		return false;
	}

	public function flush() {
		if ($this->valid) {
			ob_flush();
			return true;
		}
		return false;
	}

	public function endClean() {
		if ($this->valid) {
			if (ob_end_clean()) {
				$this->valid = false;
				return true;
			}
		}
		return false;
	}
	
	public function endFlush() {
		if ($this->valid) {
			if (ob_end_flush()) {
				$this->valid = false;
				return true;
			}
		}
		return false;
	}

	public function finish() {
		return $this->getClean();
	}
	
	public function __toString() {
		try {
			return $this->getContents();
		} catch (\Exception $e) {
			return '';
		}
	}
	
}
