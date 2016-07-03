<?php

namespace WordPress\Post;

use Closure;
use WordPress\Rewrite\Rule as RewriteRule;

/**
 * Defines a post type at runtime.
 * 
 * @property-read string $name
 * @property-read string $singular
 * @property-read string $plural
 * @property-read array $labels
 * @property-read array $args
 */
class Type
{
	
	protected $name;
	protected $singular;
	protected $plural;
	protected $labels = array();
	protected $args = array();
	
	/**
	 * @var \WordPress\Rewrite\Rule
	 */
	protected $rewriteRule;
	
	public function __construct($name, $singular = null, $plural = null) {
		if (empty($singular)) {
			$singular = str_replace(['-', '_'], ' ', ucfirst($name));
		}
		if (empty($plural)) {
			$plural = $singular.'s';
		}
		$this->name = $name;
		$this->singular = $singular;
		$this->plural = $plural;
	}
	
	public function __get($key) {
		return isset($this->$key) ? $this->$key : null;
	}
	
	public function __isset($key) {
		return isset($this->$key);
	}
	
	public function setArgs(array $args) {
		$this->args = $args;
		return $this;
	}

	public function addArgs(array $args) {
		$this->args = array_merge($this->args, $args);
		return $this;
	}
	
	public function hasArg($name) {
		return isset($this->args[$name]);
	}

	public function getArg($name) {
		return isset($this->args[$name]) ? $this->args[$name] : null;
	}
	
	public function getDefaultArg($name) {
		$defaults = $this->getDefaultArgs();
		if (isset($defaults[$name])) {
			$arg = $defaults[$name];
			return $arg instanceof Closure ? $arg() : $arg;
		}
	}

	public function setLabels(array $labels) {
		$this->labels = $labels;
		return $this;
	}
	
	public function addLabels(array $labels) {
		$this->labels = array_merge($this->labels, $labels);
		return $this;
	}
	
	public function hasLabel($name) {
		return isset($this->labels[$name]);
	}
	
	public function getLabel($name) {
		return isset($this->labels[$name]) ? $this->labels[$name] : null;
	}
	
	public function getDefaultLabel($name) {
		$labels = $this->getDefaultLabels();
		return isset($labels[$name]) ? $labels[$name] : null;
	}

	public function getAllLabels() {
		return array_merge($this->getDefaultLabels(), $this->labels);
	}
	
	public function setDescription($description) {
		$this->args['description'] = $description;
		return $this;
	}

	public function setIcon($dashicon) {
		$this->args['menu_icon'] = $dashicon;
		return $this;
	}
	
	public function setRewrite($rewrite) {
		if ($rewrite instanceof RewriteRule) {
			$this->rewriteRule = $rewrite;
		} else {
			$this->args['rewrite'] = $rewrite;
		}
		return $this;
	}
	
	public function getRewrite() {
		return isset($this->rewriteRule) ? $this->rewriteRule : null;
	}
	
	public function get($key) {
		return isset($this->args[$key]) ? $this->args[$key] : null;
	}
	
	public function has($key) {
		return isset($this->args[$key]);
	}
	
	public function set($key, $value) {
		$this->args[$key] = $value;
		return $this;
	}
	
	public function register() {
		if (! did_action('init')) {
			add_action('init', [$this, '_register'], 1);
		} else {
			$this->_register();
		}
	}
	
	public function _register() {
		$this->args['labels'] = $this->getAllLabels();
		register_post_type($this->name, $this->args);
		if ($this->rewriteRule) {
			$this->rewriteRule->_register();
		}
	}

	public function getDefaultLabels() {
		$singular = $this->singular;
		$plural = $this->plural;
		return array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'archives'              => $singular.' Archives',
			'parent_item_colon'     => 'Parent '.$singular.':',
			'all_items'             => 'All '.$plural,
			'add_new_item'          => 'Add New '.$singular,
			'add_new'               => 'Add New',
			'new_item'              => 'New '.$singular,
			'edit_item'             => 'Edit '.$singular,
			'update_item'           => 'Update '.$singular,
			'view_item'             => 'View '.$singular,
			'search_items'          => 'Search '.$singular,
			'not_found'             => 'No '.$plural.' found',
			'not_found_in_trash'    => 'No '.$plural.' found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => 'Insert into '.$singular,
			'uploaded_to_this_item' => 'Uploaded to this '.$singular,
			'items_list'            => $plural.' list',
			'items_list_navigation' => $plural.' list navigation',
			'filter_items_list'     => 'Filter '.$plural.' list',
		);
	}
	
	protected function getDefaultArgs() {
		return array(
			'label'                 => $this->getLabel('name') ?: $this->plural,		// $labels['name']
			'description'           => '',
			'public'                => false,				// false
			'hierarchical'          => false,				// false
			'menu_position'         => null,				// null (i.e. placed at the bottom)
			'menu_icon'             => 'dashicons-posts', 	// the posts icon
			'capability_type'       => 'post',				// 'post' (May be array for plurals e.g. array('story', 'stories'))
			'capabilities'			=> array(), 			// [built from get_post_type_capabilities($capability_type)]
			'map_meta_cap'			=> false,				// false Whether to to use the internal default meta capability handling.
			'supports'              => ['title', 'editor'],	// array('title', 'editor')
			'register_meta_box_cb'	=> null,				// callable
			'taxonomies'			=> array(),				// array()
			'has_archive'           => false,				// false
			'rewrite'				=> true,				// true|array
			'can_export'            => true,				// true
			'delete_with_user'		=> null,				// null|bool
			'show_in_rest'			=> false,
			'labels'                => [],
			'exclude_from_search' => function() {
				return ! $this->getArg('public');
			},
			'publicly_queryable' => function() {
				return $this->getArg('public');
			},
			'show_ui' => function() {
				return $this->getArg('public');
			},
			'show_in_menu' => function() {
				return $this->getArg('show_ui'); // Can be existing top-level page e.g. 'tools.php'
			},
			'show_in_nav_menus' => function() {
				return $this->getArg('public');
			},
			'show_in_admin_bar' => function() {
				return $this->getArg('show_in_menu');
			},
		);
	}
	
}
