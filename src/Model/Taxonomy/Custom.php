<?php

namespace WordPress\Model\Taxonomy;

abstract class Custom
{
	public $name;
	public $singular_name;
	public $label_search_items = 'Search %name%';
	public $label_popular_items = 'Popular %name%';
	public $label_all_items = 'All %name%';
	public $label_parent_item = null;
	public $label_parent_item_colon = null;
	public $label_edit_item = 'Edit %singular%';
	public $label_update_item = 'Update %singular%';
	public $label_add_new_item = 'Add new %singular%';
	public $label_new_item_name = 'New %singular% name';
	public $label_separate_items_with_commas = 'Separate %name% with commas';
	public $label_add_or_remove_items = 'Add or remove %name%';
	public $label_choose_from_most_used = 'Choose from the most used %name%';
	public $label_not_found = 'No %name% found';
	public $label_menu_name = '%name%';
	
	public $public = true;
	public $show_ui; // value of $public
	public $show_in_menu; // value of $show_ui
	public $show_in_nav_menus; // value of $public
	public $show_tagcloud; // value of $show_ui
	public $show_in_quick_edit; // value of $show_ui
	public $meta_box_cb;
	public $show_admin_column; // value of $public
	public $description;
	public $hierarchical = false;
	public $update_count_callback; // value of $public
	public $query_var = true;
	public $rewrite = true;
	public $capabilities;
	public $sort;
	
	public $object_type;
	
	private $registered = false;
	
	public function __construct() {
		if (! doing_action('init') && ! did_action('init')) {
			add_action('init', array($this, 'register'));
		}
	}
	
	public function register() {
		if (! $this->registered) {
			if (empty($this->name) || empty($this->singular_name)) {
				throw new \RuntimeException("Must set custom taxonomy 'name' and 'singular_name' properties.");
			}
			if (empty($this->object_type)) {
				throw new \RuntimeException("Must set custom taxonomy 'object_type' property.");
			}
			register_taxonomy($this->name, $this->object_type, $this->getSettings());
			$this->registered = true;
		}
	}
	
	protected function getSettings() {
		
		$settings = array(
			'labels' => array(
				'name' => $this->name,
				'singular_name' => $this->singular_name,
			),
		);
		
		foreach(get_class_vars($this) as $var => $default) {
			if (substr($var, 0, 6) === 'label_') {
				$value = str_replace(array('%name%', '%singular%'), array($this->name, $this->singular_name), $this->$var);
				$settings['labels'][substr($var, 5)] = $value;
			} else if ($var !== 'name' && $var !== 'singular_name' && $var !== 'registered' && $var !== 'object_type') {
				if (isset($this->$var)) {
					$settings[$var] = $this->$var;
				}
			}
		}
		
		return $settings;
	}
	
}
