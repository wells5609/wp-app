<?php

namespace WordPress\Post\Type;

abstract class Rewrite
{
	public function __construct() {
		
		if (doing_action('init') || did_action('init')) {
			$this->addRule();
		} else {
			add_action('init', array($this, 'addRule'), 100);
		}
		
		add_filter('post_type_link', array($this, 'getPermalink'), 100, 3);
	}
	
	public function addRule() {
		
		$tags = $this->getTags();
		
		if (! empty($tags)) {
			foreach ($tags as $tag => $regex) {
				add_rewrite_tag("%{$tag}%", $regex);
			}
		}
		
		add_rewrite_rule($this->getStructure(), $this->getRewrittenUrl(), $this->getPosition());
	}

	public function getPermalink($permalink, $post, $leavename) {
		
		if ($this->getPostType() === $post->post_type) {
			return $this->rewritePermalink($permalink, $post, $leavename);
		}
		
		return $permalink;
	}
	
	protected function getTags() {
		return array();
	}
	
	protected function getPosition() {
		return 'bottom';
	}
	
	abstract protected function getPostType();
	
	abstract protected function getStructure();
	
	abstract protected function getRewrittenUrl();
	
	abstract protected function rewritePermalink($permalink, $post, $leavename);
}
