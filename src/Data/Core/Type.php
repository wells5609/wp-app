<?php

namespace WordPress\Data\Core;

class Type extends \WordPress\Data\Type
{
	
	const POST = 'post';
	const PAGE = 'page';
	const REVISION = 'revision';
	const ATTACHMENT = 'attachment';
	const COMMENT = 'comment';
	const TAXONOMY = 'taxonomy';
	const TERM = 'term';
	const USER = 'user';

	private $name;

	public function __construct($name) {
		$type = constant(__CLASS__.'::'.strtoupper($name));
		if (null === $type) {
			throw new \InvalidArgumentException("Invalid core type '$name'");
		}
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function getModelClassname() {
		$class = 'WordPress\Data\Core\\'.ucfirst($this->name);
		return apply_filters($this->name.'_model_class', $class, $this);
	}

	protected function createStorage() {
		$class = 'WordPress\Data\Core\\'.ucfirst($this->name).'\\Storage';
		$class = apply_filters($this->name.'_storage_class', $class, $this);
		return new $class($this->createFactory());
	}

}
