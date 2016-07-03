<?php

namespace WordPress\Data\Core;

use WordPress\Data\Meta\MetadataTrait;

class Post extends AbstractModel
{
	
	use MetadataTrait;
	
	public $ID;
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_type = 'post';
	public $post_mime_type = '';
	public $comment_count = 0;

	protected static $lazy_properties = array('page_template', 'post_category', 'tags_input', 'ancestors');
	
	public function getWordPressObjectType() {
		return 'post';
	}

	public function getMetaType() {
		return 'post';
	}
	
	/**
	 * Returns the value of a property.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function readProperty($key) {
		if (isset($this->$key)) {
			return $this->$key;
		}
		if (in_array($key, static::$lazy_properties, true)) {
			return $this->lazyLoadProperty($key);
		}
	}
	
	/**
	 * Sets the value of a property.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	protected function writeProperty($key, $value) {
		$this->$key = $value;
	}

	protected function lazyLoadProperty($key) {
	
		switch ($key) {
				
			case 'page_template':
				return $this->page_template = get_post_meta($this->ID, '_wp_page_template', true);
	
			case 'post_category':
				if (is_object_in_taxonomy($this->post_type, 'category')) {
					$terms = get_the_terms($this->ID, 'category');
					if (empty($terms)) {
						$this->post_category = array();
					} else {
						$this->post_category = wp_list_pluck($terms, 'term_id');
					}
				}
				return $this->post_category;
	
			case 'tags_input':
				if (is_object_in_taxonomy($this->post_type, 'post_tag')) {
					$terms = get_the_terms($this->ID, 'post_tag');
					if (empty($terms)) {
						$this->tags_input = array();
					} else {
						$this->tags_input = wp_list_pluck($terms, 'name');
					}
				}
				return $this->tags_input;
					
			case 'ancestors':
				return $this->ancestors = get_post_ancestors($this);
					
			default: break;
		}
		
		throw new \InvalidArgumentException("Invalid lazy-loaded property '$key'");
	}
	
}
