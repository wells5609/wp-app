<?php

namespace WordPress\Theme;

class Breadcrumbs 
{
	
	/**
	 * @var \WordPress\Post\Post
	 */	
	protected $post;
	
	/**
	 * @var array
	 */
	protected $items = array();
	
	/**
	 * @var boolean
	 */
	protected $back_link;
	
	public function __construct($back_link = false) {
		$this->post = current_post();
		$this->back_link = $back_link;
	}
	
	public function prependItem($text, $href) {
		array_unshift($this->items, array($text, $href));
		return $this;
	}
	
	public function addItem($text, $href) {
		array_push($this->items, array($text, $href));
		return $this;
	}
	
	public function getItem($index) {
		return isset($this->items[$index]) ? $this->items[$index] : null;
	}
	
	public function __toString() {
		if (false === $this->build()) {
			return '';
		}
		return $this->getHTML();
	}
	
	protected function getHTML() {
		
		$s = '<ol class="breadcrumb">';
		$s .= $this->getItemHTML('Home', home_url('/'));
		
		foreach($this->items as $index => $array) {
			list($text, $href) = $array;
			$s .= $this->getItemHTML($text, $href);
		}
		
		$s .= $this->getCurrentItemHTML();
		
		if ($this->back_link) {
			isset($text) or $text = 'Home';
			isset($href) or $href = home_url('/');
			$s .= $this->getBackLinkHTML($text, $href);
		}
		
		$s .= '</ol>';
		
		return $s;
	}
	
	protected function getItemHTML($text, $href) {
		return '<li><a href="'.esc_attr($href).'">'.$text.'</a></li>';
	}
	
	protected function getCurrentItemHTML() {
		return '<li class="active">'.$this->post->post_title.'</li>';
	}
	
	protected function getBackLinkHTML($text, $href) {
		$str = is_string($this->back_link) ? $this->back_link : 'Back to '.$text;
		return '<span class="pull-right hidden-xs"><a href="'.esc_attr($href).'">'
			.'<i class="fa fa-chevron-circle-left"></i> '.$str.'</a></span>';
	}
	
	protected function allow() {
		
		// put is_*() checks here and return false to disallow
		
		return true;
	}
	
	protected function build() {
		if ($this->allow()) {
			$method = 'build_'.$this->post->post_type;
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}
		return false;
	}

	protected function build_post() {
			
		// don't show breadcrumbs for posts of type "post"
		
		return false;
	}
	
	protected function build_page() {
			
		// don't show breadcrumbs for posts of type "page"
		
		return false;
	}

}
