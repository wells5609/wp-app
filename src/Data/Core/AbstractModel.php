<?php

namespace WordPress\Data\Core;

use WordPress\Data\Model;

abstract class AbstractModel extends Model
{

	/**
	 * Returns the WordPress core object type.
	 * 
	 * Core object types are: post, page, comment, revision, attachment, taxonomy, term, and user.
	 * 
	 * @return string
	 */
	public function getWordPressObjectType() {
		return basename(str_replace('\\', '/', strtolower(get_class($this))));
	}
	
}
