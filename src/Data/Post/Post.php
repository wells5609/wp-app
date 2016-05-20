<?php

namespace WordPress\Data\Post;

use WP_Post;
use WordPress\App;
use WordPress\Data\Entity;

class Post extends Entity
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
	
	public static function find(array $args) {
		return di('posts')->find($args);
	}
	
	public static function findOne(array $args) {
		return di('posts')->findOne($args);
	}
	
	public function __construct($data = null) {
		if (isset($data) && is_numeric($data)) {
			$data = get_post($data);
		}
		parent::__construct($data);
	}
	
	public function hydrate($data) {
		if ($data instanceof WP_Post) {
			$data = $data->to_array();
		}
		parent::hydrate($data);
	}
	
	public function getRepository() {
		return di('posts');
	}
	
	protected function propertyGet($var) {
		if (property_exists($this, $var)) {
			return $this->$var;
		} else if (property_exists($this, 'post_'.$var)) {
			return $this->{'post_'.$var};
		}
		return null;
	}
	
	protected function propertyExists($var) {
		return property_exists($this, $var) || property_exists($this, 'post_'.$var);
	}
	
	protected function propertySet($var, $value) {
		if (property_exists($this, $var)) {
			$this->$var = $value;
		} else if (property_exists($this, 'post_'.$var)) {
			$this->{'post_'.$var} = $value;
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
		
		#if ($app->hasTemplateVars()) {
		#	extract($app->getTemplateVars(), EXTR_SKIP);
		#}
		
		include $__file;
	}
	
	public function getUri() {
		if (! isset($this->uri) && isset($this->ID)) {
			$this->uri = get_permalink($this->ID);
		}
		return $this->uri;
	}
	
	public function getPostType() {
		return $this->post_type;
	}

	public function isCustomType() {
		return ! in_array($this->post_type, array('post', 'page', 'attachment', 'revision', 'nav_menu_item'));
	}
	
	public function getTitleLink() {
		return '<a href="'.esc_attr($this->getUri()).'">'.$this->post_title.'</a>';
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
	
	public function save() {
		return $this->getRepository()->save($this);
	}
	
	public function __toString() {
		return $this->post_title;
	}
	
}
