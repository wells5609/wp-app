<?php

namespace WordPress\Storage;

abstract class FilesystemStorage implements StorageInterface
{
	
	/**
	 * Filesystem path.
	 * 
	 * @var string
	 */
	protected $path;
	
	/**
	 * Constructor.
	 * 
	 * @param string $path
	 */
	public function __construct($path) {
		$this->setPath($path);
	}
	
	/**
	 * Sets the filesystem path.
	 * 
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = realpath($path) ?: $path;
	}
	
	/**
	 * Returns the filesystem path.
	 * 
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * Returns a unique identifier for the storage instance.
	 * 
	 * Identifiers need only be unique for its type.
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->path;
	}
	
	/**
	 * Creates a record from data.
	 * 
	 * @param array $data
	 * 
	 * @return RecordInterface
	 */
	public function create(array $data) {
		return new Record($data);
	}
	
	protected function encode($data) {
		return serialize($data);
	}
	
	protected function decode($data) {
		return unserialize($data);
	}
	
}
