<?php

namespace WordPress\Post;

use WP_Post;
use WordPress\App;

class Post
{	
	public $ID;
	public $post_author;
	public $post_date;
	public $post_date_gmt;
	public $post_content;
	public $post_title;
	public $post_excerpt;
	public $post_status;
	public $comment_status;
	public $ping_status;
	public $post_password;
	public $post_name;
	public $to_ping;
	public $pinged;
	public $post_modified;
	public $post_modified_gmt;
	public $post_content_filtered;
	public $post_parent;
	public $guid;
	public $menu_order;
	public $post_type;
	public $post_mime_type;
	public $comment_count;
	public $filter;
	public $ancestors = array();
	public $page_template;
	public $post_category;
	public $tags_input;
	
	protected $uri;
	protected $meta;
	
	public static function findAll(array $args) {
		$posts = get_posts($args);
		return empty($posts) ? null : array_map('custom_post', $posts);
	}
	
	public static function findOne(array $args) {
		$posts = get_posts($args);
		return empty($posts) ? null : custom_post(reset($posts));
	}
	
	public function __construct($data) {
		if (is_numeric($data)) {
			$data = get_post($data);
		}
		$this->import($data);
		$this->uri = get_permalink($this->ID);
		$this->onConstruct();
	}
	
	public function __get($var) {
		if (isset($this->$var)) {
			return $this->$var;
		} else if (isset($this->{'post_'.$var})) {
			return $this->{'post_'.$var};
		}
		return null;
	}
	
	public function __isset($var) {
		return isset($this->$var) || isset($this->{'post_'.$var});
	}
	
	public function __set($var, $value) {
		if (property_exists($this, $var)) {
			$this->$var = $value;
		} else if (property_exists($this, 'post_'.$var)) {
			$this->{'post_'.$var} = $value;
		}
	}
	
	public function import($data) {
		
		if ($data instanceof WP_Post) {
			$data = $data->to_array();
		} else if (! is_array($data)) {
			$data = (array)$data;
		}
		
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function includeFile($__file) {
		
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
		
		$app = App::instance();
		
		/** 
		 * @var \WordPress\Theme\ActiveTheme
		 */
		$theme = $app->get('theme');
		
		if (! empty($wp_query->query_vars) && is_array($wp_query->query_vars)) {
			extract($wp_query->query_vars, EXTR_SKIP);
		}
		
		if ($app->hasTemplateVars()) {
			extract($app->getTemplateVars(), EXTR_SKIP);
		}
		
		include $__file;
	}
	
	public function getUri() {
		return $this->uri;
	}
	
	public function getPostType() {
		return $this->post_type;
	}

	public function isCustomType() {
		return ! in_array($this->post_type, array('post', 'page', 'attachment', 'revision', 'nav_menu_item'));
	}
	
	public function getTitleLink() {
		return '<a href="'.esc_attr($this->uri).'">'.$this->post_title.'</a>';
	}
	
	public function getMeta($key = null, $single = false) {
	
		if (! isset($this->meta)) {
			$this->meta = get_post_meta($this->ID);
		}
		
		if (null === $key) {
			return $this->meta;
		} else if (isset($this->meta[$key])) {
			if ($single && is_array($this->meta[$key])) {
				return reset($this->meta[$key]);
			}
			return $this->meta[$key];
		}
		
		return null;
	}
	
	public function update() {
		return wp_update_post($this->toUpdateArray(), true);
	}
	
	public function toArray() {
		return get_object_vars($this);
	}
	
	public function toUpdateArray() {
		return get_object_public_vars($this);
	}
	
	public function __toString() {
		return $this->post_title;
	}
	
	protected function onConstruct() {}
	
}
