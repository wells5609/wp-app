<?php

namespace WordPress\Post;

class Query 
{
	
	protected $args;
	protected $results;
	
	public function __construct($args = array()) {
		$this->args = wp_parse_args($args, array(
			'post_type' => 'any',
			'post_status' => 'publish',
		));
	}
	
	public function type($value) {
		$this->args['post_type'] = $value;
		return $this;
	}
	
	public function status($value) {
		$this->args['post_status'] = $value;
		return $this;
	}
	
	public function num($value) {
		$this->args['numberposts'] = (int)$value;
		return $this;
	}
	
	public function offset($value) {
		$this->args['offset'] = (int)$value;
		return $this;
	}

	public function order($value) {
		$this->args['order'] = strtoupper($value);
		return $this;
	}
	
	public function orderby($value) {
		$this->args['orderby'] = $value;
		return $this;
	}
	
	public function meta($key, $value = null, $compare = null, $type = null) {
		
		if (! isset($this->args['meta_query'])) {
			$this->args['meta_query'] = array();
		}
		
		$query = array('key' => $key);
		
		if (isset($value)) {
			$query['value'] = $value;
		}
		
		if (isset($compare)) {
			$query['compare'] = $compare;
		}
		
		if (isset($type)) {
			$query['type'] = $type;
		}
		
		$this->args['meta_query'][] = $query;
		
		return $this;
	}
	
	public function metaRelation($relation) {
		
		if (! isset($this->args['meta_query'])) {
			$this->args['meta_query'] = array();
		}
		
		$this->args['meta_query']['relation'] = strtoupper($relation);
		
		return $this;
	}
	
	public function tax($tax, $terms = null, $field = 'term_id', $operator = 'IN') {
		
		if (! isset($this->args['tax_query'])) {
			$this->args['tax_query'] = array();
		}
		
		$query = array('taxonomy' => $tax);
		
		if (isset($terms)) {
			$query['terms'] = $terms;
			$query['field'] = strtolower($field);
			$query['operator'] = strtoupper($operator);
		}
		
		$this->args['tax_query'][] = $query;
		
		return $this;
	}
	
	public function taxRelation($relation) {
		
		if (! isset($this->args['tax_query'])) {
			$this->args['tax_query'] = array();
		}
		
		$this->args['tax_query']['relation'] = strtoupper($relation);
		
		return $this;
	}
	
	public function __invoke() {
		$this->results = get_posts($this->args);
		return $this;
	}
	
	public function execute() {
		return $this();
	}
	
	public function getResults() {
		return isset($this->results) ? $this->results : null;
	}
}
