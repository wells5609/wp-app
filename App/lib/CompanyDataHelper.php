<?php

class CompanyDataHelper {

	static $cdp_base_url = 'https://www.cdproject.net/en-US/Results/Pages/Company-Responses.aspx?company=';
	
	static protected $_instance;
		
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
		
	function cdp_url( $cdp_company_id ){
		return self::$cdp_base_url . $cdp_company_id;	
	}
	
	function term_display( $term, $taxonomy, $label = true, $description = false ){
		$str = '';
		
		if ( !is_object($term) )
			$term = get_term_by('slug', $term, $taxonomy);
		
		$text = $tooltip = $term->name;
		
		if ( $description && !empty($term->description) ) 
			$text .= ': ' . $term->description;
		
		if ( $label ){
			if ( is_string($label) ){
				$tooltip .= ' ' . $label;
			}
			else { 
				$tax = get_taxonomy($taxonomy);
				$label = $tax->labels->singular_name;
			}
			$str .= '<span class="text-label text-sm">' . $label . ': </span>';
		}
		
		$str .= '<a href="' . esc_url(get_term_link($term->name, $taxonomy)) . '" title="' . esc_attr($tooltip) . '">' . $text . '</a>';
		
		return $str;	
	}
	
	function get_the_terms( $id, $taxonomy, $args = array('one') ){
		
		$term_list = get_the_terms($id, $taxonomy);
		
		if ( !empty($args) ){
			
			if ( in_array('parents', $args) || in_array('parent', $args) )
				$terms = wp_list_filter($term_list, array('parent' => 0));
			
			if ( in_array('children', $args) || in_array('child', $args) )
				$terms = wp_list_filter($term_list, array('parent' => 0), "NOT");
			
			if ( in_array('one', $args) || in_array('child', $args) || in_array('parent', $args) )
				$terms = array_shift($terms);
		}
		
		return $terms;
	}
	
	
	// No construction
	private function __construct(){	
	}
		
}


?>