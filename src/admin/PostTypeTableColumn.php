<?php

namespace WordPress\Admin;

use WP_Query;

abstract class PostTypeTableColumn
{	
	protected $post_type;

	public function __construct() {
		$this->post_type = $this->getPostType();
		add_filter("manage_edit-{$this->post_type}_columns", array($this, 'columns'));
		add_action("manage_{$this->post_type}_posts_custom_column", array($this, 'content'), 10, 2);
		if ($this->isSortable()) {
			add_filter("manage_edit-{$this->post_type}_sortable_columns", array($this, 'sortable'));
			add_action('pre_get_posts', array($this, 'orderby'));
		}
	}
	
	abstract public function getPostType();
	
	abstract public function isSortable();

	public function columns($columns) {
		return $columns;
	}

	public function content($column_name, $post_id) {
		echo '';
	}

	public function sortable($columns) {
		// To make a column 'un-sortable' remove it from the array
		// e.g. unset($columns['date']);
		return $columns;
	}

	public function orderby(WP_Query $query) {

		if (! is_admin()) {
			return;
		}
		
		// ...
	}

}
