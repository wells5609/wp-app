<?php

namespace WordPress\Storage\Filesystem;

use WordPress\Storage\FilesystemStorage;
use WordPress\Storage\RecordInterface;
use WordPress\Storage\Record;

class FileStorage extends FilesystemStorage
{
	
	protected $records;
	protected $dirty = false;
	
	/**
	 * Returns an alphanumeric string describing the type of storage container.
	 * 
	 * @return string
	 */
	public function getTypeName() {
		return 'file';
	}
	
	public function isDirty() {
		return $this->dirty;
	}
	
	public function __destruct() {
		if ($this->isDirty()) {
			file_put_contents($this->getPath(), $this->encode($this->records), LOCK_EX);
		}
	}
	
	/**
 	 * Retrieves multiple records from storage.
	 *
	 * @param mixed $args
	 * 
	 * @return mixed
	 */
	public function fetch($args) {
		
		$args = $this->getArrayArgs($args);
		$records = $this->getRecords();
		
		if (empty($records)) {
			return null;
		}
		
		if (empty($args)) {
			return $records;
		}
		
		if (isset($args['uid'])) {
			return isset($records[$args['uid']]) ? array($records[$args['uid']]) : null;
		}
		
		$results = array();
		
		if (isset($args['count'])) {
			$limit = $args['count'];
			unset($args['count']);
		}
		
		foreach($records as $uid => $record) {
			foreach($args as $key => $value) {
				if (! isset($record->$key) || $record->$key != $value) {
					continue 2;
				}
			}
			$results[$uid] = $record;
		}
		
		if (isset($limit)) {
			
			if (empty($results)) {
				
				// No arguments were given, so extract from all
				if (empty($args)) {
					return array_slice($records, 0, $limit, true);
				}
				
				// Args were given but no records were found
				return array();
			}
			
			// Extract $limit number of records from $results
			return array_slice($results, 0, $limit, true);
		}
		
		return $results;
	}
	
	/**
 	 * Retrieves a single record from storage.
	 *
	 * @param mixed $args
	 * 
	 * @return mixed
	 */
	public function fetchOne($args) {
		
		$args = $this->getArrayArgs($args);
		$records = $this->getRecords();
		
		if (empty($records)) {
			return null;
		}
		
		if (isset($args['uid'])) {
			return isset($records[$args['uid']]) ? $records[$args['uid']] : null;
		}
		
		if (isset($args['count'])) {
			unset($args['count']);
		}
		
		foreach($records as $record) {
			foreach($args as $key => $value) {
				if (! isset($record->$key) || $record->$key != $value) {
					continue 2;
				}
				return $record;
			}
		}
		
		return null;
	}
	
	/**
	 * Saves a record to storage.
	 * 
	 * @param RecordInterface $record
	 */
	public function save(RecordInterface $record) {
		
		if (! $pk = $record->getPrimaryKey()) {
			$class = get_class($record);
			$pk = $class::generatePrimaryKey($record);
		}
		
		$this->records = $this->getRecords();
		$this->records[$pk = $record->getPrimaryKey()] = $record;
		$this->dirty = true;
	}
	
	/**
	 * Deletes a record from storage.
	 * 
	 * @param RecordInterface $record
	 */
	public function delete(RecordInterface $record) {
			
		if (! $pk = $record->getPrimaryKey()) {
			return;
		}
		
		$records = $this->getRecords();
		
		if (isset($records[$pk])) {
			unset($records[$pk]);
			$this->records = $records;
			$this->dirty = true;
		}
	}
	
	protected function getRecords() {
		if (! isset($this->records)) {
			if (! file_exists($this->getPath())) {
				return array();
			}
			$this->records = $this->decode(file_get_contents($this->getPath()));
		}
		return $this->records;
	}
	
	protected function getArrayArgs($args) {
		
		if (empty($args)) {
			return array();
		}
		
		if (is_array($args)) {
			return $args;
		}
		
		if (is_string($args)) {
			return array('id' => $args);
		}
		
		if (is_int($args)) {
			return array('count' => $args);
		}
		
		throw new \InvalidArgumentException("Could not return arguments as array, given: ".gettype($args));
	}
	
}
