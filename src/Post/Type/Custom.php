<?php

namespace WordPress\Post\Type;

use RuntimeException;

class Custom
{
	// Required
	public $slug;
	public $name;
	public $singular_name;
	
	// Optional
	public $class;
	
	public $label_menu_name = '%name%';
	public $label_name_admin_bar = '%singular%';
	public $label_add_new = 'Add new';
	public $label_add_new_item = 'Add new %singular%';
	public $label_new_item = 'New %singular%';
	public $label_edit_item = 'Edit %singular%';
	public $label_view_item = 'View %singular%';
	public $label_all_items = 'All %name%';
	public $label_search_items = 'Search %name%';
	public $label_parent_item_colon = 'Parent %name%:';
	public $label_not_found = 'No %name% found';
	public $label_not_found_in_trash = 'No %name% found in trash';
	
	public $description;
	public $public = true;
	public $exclude_from_search; // opposite of $public
	public $publicly_queryable; // value of $public
	public $show_ui; // value of $public
	public $show_in_nav_menus; // value of $public
	public $show_in_menu; // value of $show_ui
	public $show_in_admin_bar; // value of $show_in_menu
	public $menu_position;
	public $menu_icon;
	public $capability_type; // 'post'
	public $supports = array('title', 'editor');
	public $has_archive = false;
	public $hierarchical = false;
	public $rewrite = true;
	public $query_var = true;
	public $can_export = true;
	public $show_in_rest = false;
	public $rest_base;
	public $rest_control_class;
	public $taxonomies;
	public $register_meta_box_cb;
	
	public function __construct(array $args = null) {
		
		if (isset($args)) {
			$this->fromArray($args);
		}
		
		if (empty($this->name) || empty($this->singular_name)) {
			throw new RuntimeException("Must set custom post type 'name' and 'singular_name' properties.");
		}
		
		if (empty($this->slug)) {
			$this->slug = str_replace(' ', '_', strtolower($this->name));
		}
	}
	
	public function register() {
		register_post_type($this->slug, $this->getSettings());
	}
	
	protected function fromArray(array $args) {
		
		if (isset($args['labels'])) {
			foreach($args['labels'] as $type => $value) {
				if ($type === 'name' || $type === 'singular_name') {
					$this->$type = $value;
				} else {
					$this->{"label_".$type} = $value;
				}
			}
			unset($args['labels']);
		}
		
		foreach($args as $prop => $value) {
			$this->$prop = $value;
		}
	}
	
	protected function getSettings() {
		
		$settings = array(
			'labels' => array(
				'name' => $this->name,
				'singular_name' => $this->singular_name,
			),
		);
		
		foreach(get_object_vars($this) as $var => $value) {
			if (substr($var, 0, 6) === 'label_') {
				$settings['labels'][substr($var, 6)] = $this->filterLabel($this->$var);
			} else if (null !== $value) {
				$settings[$var] = $value;
			}
		}
		
		return $settings;
	}
	
	protected function filterLabel($label) {
		return str_replace(array('%name%', '%singular%'), array($this->name, $this->singular_name), $label);
	}
	
}
