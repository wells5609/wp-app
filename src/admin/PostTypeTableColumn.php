<?php

namespace WordPress\Admin;

use WP_Query;

abstract class PostTypeTableColumn
{	
	protected $post_type;
	protected $sortable;

	public function __construct($post_type, $sortable = false) {
		
		$this->post_type = $post_type;
		$this->sortable = $sortable;
		
		add_filter("manage_edit-{$post_type}_columns", array($this, 'columns'));
		add_action("manage_{$post_type}_posts_custom_column", array($this, 'content'), 10, 2);
		
		if ($sortable) {
			add_filter("manage_edit-{$post_type}_sortable_columns", array($this, 'sortable'));
			add_action('pre_get_posts', array($this, 'orderby'));
		}
	}
	
	public function getPostType() {
		return $this->post_type;
	}
	
	public function isSortable() {
		return $this->sortable;
	}

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
