<?php

namespace WordPress\Post;

use WP_Query;

/**
 * Defines a column to display in the admin list table for a given post type.
 */
abstract class ListTableColumn
{	
	
	protected $post_type;
	protected $sortable;

	public function __construct($post_type, $sortable = null) {
		
		if (is_object($post_type)) {
			if ($post_type instanceof \WP_Post) {
				$post_type = $post_type->post_type;
			} else {
				$post_type = $post_type->name;
			}
		}
		
		$this->post_type = $post_type;
		$this->sortable = (bool)$sortable;
		
		add_filter("manage_edit-{$post_type}_columns", array($this, 'columns'));
		add_action("manage_{$post_type}_posts_custom_column", array($this, 'content'), 10, 2);
		
		if ($this->sortable) {
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
	
	/**
	 * Add to the column name array.
	 * 
	 * @param array $columns
	 * 
	 * @return array
	 */
	public function columns($columns) {
		return $columns;
	}
	
	/**
	 * Output the content for the given column and post (i.e. the list table cell content).
	 * 
	 * @param string $column_name
	 * @param number $post_id
	 * 
	 * @return void
	 */
	public function content($column_name, $post_id) {
		echo '';
	}
	
	/**
	 * Add or remove sortable columns.
	 * 
	 * @param array $columns
	 * 
	 * @return array
	 */
	public function sortable($columns) {
		// To make a column 'un-sortable' remove it from the array
		// e.g. unset($columns['date']);
		return $columns;
	}
	
	/**
	 * Change WP_Query to effect the desired sort order.
	 * 
	 * @param WP_Query $query
	 * 
	 * @return void
	 */
	public function orderby(WP_Query $query) {

		if (! is_admin()) {
			return;
		}
		
		// ...
	}

}
