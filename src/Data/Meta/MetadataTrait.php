<?php

namespace WordPress\Data\Meta;

trait MetadataTrait
{

	protected $metadata;

	abstract public function getMetaType();

	public function getMetaObjectId() {
		return $this->ID;
	}

	public function getMeta($key) {
		if (! isset($this->metadata)) {
			$this->metadata = $this->fetchAllMetadata();
		}
		#if (! array_key_exists($key, $this->metadata)) {
		#	$this->metadata[$key] = $this->fetchMetadata($key);
		#}
		return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
	}

	public function setMeta($key, $value) {
		$this->metadata[$key] = $value;
	}

	public function hasMeta($key) {
		return $this->getMeta($key) !== null;
	}

	public function deleteMeta($key) {
		unset($this->metadata[$key]);
	}

	public function getMetadata() {
		if (! isset($this->metadata)) {
			$this->metadata = $this->fetchAllMetadata();
		}
		return $this->metadata;
	}

	public function getPublicMetadata() {
		$filtered = array();
		foreach($this->getMetadata() as $key => $value) {
			if ($key[0] !== '_') {
				$filtered[$key] = $value;
			}
		}
		return $filtered;
	}

	public function getProtectedMetadata() {
		return array_diff_key($this->getMetadata(), $this->getPublicMetadata());
	}

	protected function fetchAllMetadata() {
		return get_metadata($this->getMetaType(), $this->getMetaObjectId());
	}

	protected function fetchMetadata($key, $single = false) {
		return get_metadata($this->getMetaType(), $this->getMetaObjectId(), $key, $single);
	}

}
