<?php

namespace WordPress\Data\Core\Revision;

use WordPress\Data\Core\Post\Storage as PostStorage;

class Storage extends PostStorage
{
	
	protected $defaultPostArgs = array(
		'post_type' => 'revision',
	);
	
}
