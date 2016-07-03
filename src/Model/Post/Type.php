<?php

namespace WordPress\Model\Post;

use WordPress\Model\Post;

class Type
{
	
	/**
	 * Object instance cache.
	 * 
	 * @var array
	 */
	protected static $instances = [];
	
	/**
	 * Whether the post-type has been registered.
	 * 
	 * @var boolean
	 */
	protected $registered = false;
	
	public $name;
	public $label;
	public $labels = array();
	public $description = '';
	public $public = false;
	public $hierarchical = false;
	public $exclude_from_search = null;
	public $publicly_queryable = null;
	public $show_ui = null;
	public $show_in_menu = null;
	public $show_in_nav_menus = null;
	public $show_in_admin_bar = null;
	public $menu_position = null;
	public $menu_icon = null;
	public $capability_type = 'post';
	public $capabilities = array();
	public $map_meta_cap = null;
	public $supports = array();
	public $register_meta_box_cb = null;
	public $taxonomies = array();
	public $has_archive = false;
	public $rewrite = true;
	public $query_var = true;
	public $can_export = true;
	public $delete_with_user = null;
	public $_edit_link = 'post.php?post=%d';
	
	/**
	 * Returns an instance of the object.
	 * 
	 * @param string $post_type
	 * @return \WordPress\Model\Post\Type
	 */
	public static function instance($post_type) {
		if (! isset(static::$instances[$post_type])) {
			return new static($post_type);
		}
		return static::$instances[$post_type];
	}
	
	/**
	 * Constructor.
	 * 
	 * @param string $post_type
	 * @return void
	 */
	public function __construct($post_type) {
	
		if (is_object($post_type)) {
			$post_type = $post_type->post_type;
		}
		
		$this->name = $post_type;
	
		if (isset($GLOBALS['wp_post_types'][$post_type])) {
			$this->import($GLOBALS['wp_post_types'][$post_type]);
			$this->registered = true;
		}
		
		static::$instances[$post_type] = $this;
	}
	
	/**
	 * Imports data into the object.
	 * 
	 * @param mixed $data
	 * @return void
	 */
	public function import($data) {
		if (! is_array($data)) {
			$data = is_object($data) ? get_object_vars($data) : (array)$data;
		}
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * Returns a label for the post type.
	 * 
	 * @param string $kind [Optional]
	 * @return string
	 */
	public function getLabel($kind = null) {
		if (! isset($kind)) {
			return $this->label;
		}
		return isset($this->labels->$kind) ? $this->labels->$kind : null;
	}
	
	/**
	 * Returns the type-specific capability for a given capability.
	 * 
	 * @param string $name
	 * @return string
	 */
	public function getCapability($name) {
		return isset($this->cap->$name) ? $this->cap->$name : null;
	}
	
	/**
	 * Returns whether the post type has been registered.
	 * 
	 * @return boolean
	 */
	public function isRegistered() {
		return $this->registered;
	}
	
	/**
	 * Checks whether the post type is built-in.
	 * 
	 * @return boolean
	 */
	public function isBuiltin() {
		return in_array($this->name, array('post', 'page', 'attachment', 'revision', 'nav_menu_item'), true);
	}
	
	/**
	 * Checks whether the post is a custom type.
	 * 
	 * @return boolean
	 */
	public function isCustom() {
		return ! $this->isBuiltin();
	}
	
	/**
	 * Checks whether the post type is "viewable".
	 * 
	 * @return boolean
	 */
	public function isViewable() {
		return $this->publicly_queryable || ($this->public && $this->isBuiltin());
	}
	
	/**
	 * Registers the post type.
	 * 
	 * @return boolean
	 */
	public function register() {
		if (! $this->isRegistered()) {
			$args = get_object_vars($this);
			unset($args['name'], $args['registered']);
			register_post_type($this->name, $args);
			$this->registered = isset($GLOBALS['wp_post_types'][$this->name]);
		}
		return $this->registered;
	}
	
}
