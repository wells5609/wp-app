<?php

class CompanyHelper {

	public $cdp_base_url = 'https://www.cdproject.net/en-US/Results/Pages/Company-Responses.aspx?company=';
	
	
	function has_cdp( Postx_Object $company ){
		
		return ( !empty($company->cdp_score) ) ? true : false;	
	}
		
	function get_cdp_url( Postx_Object $company ){
		
		if ( !$this->has_cdp($company) )
			return '';
		
		$model =& get_meta_model('company');
		
		$cdp_id = $model->get_value($company->id, 'cdp_company_id');
		
		return $this->cdp_base_url . $cdp_id;	
	}
	
	function term_display( $term, $taxonomy, $label = true, $description = false ){
		$str = '';
		
		if ( !is_object($term) ){
			$term = get_term_by('slug', $term, $taxonomy);
		}
		if ( !is_object($term) )
			return '';
			
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
			$str .= text_label($label . ': ', 'class="text-sm"');
		}
		
		$str .= '<a href="' . esc_url(get_term_link($term, $taxonomy)) . '" title="' . esc_attr($tooltip) . '">' . $text . '</a>';
		
		return $str;	
	}
	
	function get_the_terms( $company, $taxonomy, $args = array('one') ){
		
		$id = get_company_id($company);
		$term_list = get_the_terms($id, $taxonomy);
		
		if ( !empty($args) ){
			
			if ( in_array('parents', $args) || in_array('parent', $args) )
				$terms = wp_list_filter($term_list, array('parent' => 0));
			
			elseif ( in_array('children', $args) || in_array('child', $args) )
				$terms = wp_list_filter($term_list, array('parent' => 0), "NOT");
				
			if ( in_array('one', $args) || in_array('child', $args) || in_array('parent', $args) )
				$terms = array_shift($terms);
		}
		return $terms;
	}
	
	function display_addresses( $company, $columns = false ){
		
		$s = $before = $after = '';
		$id = get_company_id($company);
		$model =& get_meta_model('company');
		$addresses = $model->get_value($id, 'addresses');
		
		if ( $addresses ){
			$add = json_decode($addresses);
			
			if ($columns) {
				$s .= '<div class="row">';
				$before = '<div class="col-md-6">';
				$after = '</div>';
			}
			
			foreach($add as $title => $obj){
				
				$s .= $before . '<address id="' . $title . '-address"><b>' . ucfirst($title) . ' Address</b>';
				foreach((array)$obj as $k => $v){
					$s .= '<br>'. text_label(ucfirst($k) . ': ') . ucwords(strtolower($v));	
				}
				$s .= '</address>' . $after;
			}
			
			if ($columns){
				$s .= '</div>';	
			}
			
		}
		return $s;
	}
	
	function listing_overview( Company_Object $company, $show_excerpt = true ) {
		$s = '';
		
		$s .= '<h2><a href="' . esc_url(get_permalink($company->id)) . '">' . $company->post->post_title 
			. ' <small>' 
				. '<span class="pull-right" rel="tooltip" title="Market Cap in Billion USD$">' . $company->marketcap . '</span> ' 
				. $company->exchange . ':' . $company->ticker 
			. '</small></a></h2>';
		
		if ( $show_excerpt && isset($company->post->post_excerpt) )
			$s .= '<p class="text-muted">' . $company->post->post_excerpt . '</p>';
		
		return $s;
	}
	
	// No construction
	function __construct(){}	
}
