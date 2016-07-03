<?php

namespace WordPress\Post\Type;

use WordPress\Rewrite\Rule;
use WP_Post;

abstract class RewriteRule extends Rule
{
	
	protected $postType = 'post';

	abstract public function rewritePostLink($permalink, WP_Post $post, $leavename);
	
	public function __construct($regex = null) {
		parent::__construct($regex);
		add_filter('post_type_link', array($this, 'getPermalink'), 200, 3);
	}
	
	public function getPostType() {
		return $this->postType;
	}
	
	public function setPostType($postType) {
		$this->postType = $postType;
		return $this;
	}
	
	public function getPermalink($permalink, WP_Post $post, $leavename) {
		if ($this->getPostType() === $post->post_type) {
			return $this->rewritePostLink($permalink, $post, $leavename);
		}
		return $permalink;
	}

	protected function url($path) {
		return home_url($path);
	}

}
