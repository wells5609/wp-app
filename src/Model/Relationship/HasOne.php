<?php

namespace WordPress\Model\Relationship;

use WordPress\Model\ModelInterface;

class HasOne extends Relationship
{
	
	public function getRelatedRecords(ModelInterface $model) {
		if (! $lookupVal = $model->readAttribute($this->key)) {
			return null;
		}
		$record = $this->relatedDefinition->getStorage()->findOne(array($this->foreignKey => $lookupVal));
		if (empty($record)) {
			return $record;
		}
		$class = $this->relatedDefinition->getClassName();
		return $class::forgeObject($record);
	}
	
}
