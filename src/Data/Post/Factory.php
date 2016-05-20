<?php

namespace WordPress\Data\Post;

use WP_Post;
use WordPress\Data\Factory as BaseFactory;

class Factory extends BaseFactory
{
	
	protected $defaultClass = 'WordPress\Data\Post\Post';
	
	public function create(WP_Post $post) {
		return $this($post, $this->getClass($post->post_type));
	}
	
}
