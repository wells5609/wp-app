<?php

namespace WordPress\Model\Relationship;

use WordPress\Model\ModelInterface;

class HasMany extends Relationship
{
	
	public function getRelatedRecords(ModelInterface $model) {
		if (! $lookupVal = $model->readAttribute($this->key)) {
			return null;
		}
		$records = $this->relatedDefinition->getStorage()->find(array($this->foreignKey => $lookupVal));
		if (empty($records) || ! is_array($records)) {
			return $records;
		}
		$class = $this->relatedDefinition->getClassName();
		return array_map($class.'::forgeObject', $records);
	}
	
}
