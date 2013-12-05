<?php

class Company_ApiController extends Api_Controller {
	
	public $methods_require_apikey = array(
		'get_marketcap',
		'get_company',
		'company_reserves',
		'get_reserves',
	);
	
	static protected $_instance;
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	function get_marketcap( $q ){
		
		global $api;
		
		if ( !$this->validate_apikey_if_required(__FUNCTION__) ){
			return 'Invalid API key';
		}
				
		$mktcap = get_company_marketcap( get_company_id_from_ticker($q), false, false, true);
		
		if ( !$mktcap ){
			$api->query->ajax_action = 'update_company_marketcap';
			do_action( 'wp_ajax_' . $api->query->ajax_action, false );	
		}
		
		if ( $api->is_xml() ){
			return array('marketcap' => $mktcap);	
		}
		
		return $mktcap;
	}
	
	function get_company( $q ){
		global $api;
		
		if ( !$this->validate_apikey_if_required(__FUNCTION__) ){
			return 'Invalid API key';
		}
		
		$model =& get_model('company');
		
		$id = get_company_id_from_ticker( $q );
		
		$company = $model->query_by( 'id', $id );
		
		addTitleToObject($company);
		
		unset($company->id);
				
		if ( !$api->is_json() && !$api->is_xml() ){
			return 'Invalid output format';	
		}
		
		return $company;
	}
	
	function get_companies( $q ){
		global $api;
		
		$args = wp_parse_args( $api->query->args, array(
			'order' => 'desc',
			'orderby' => 'marketcap',
		));
		
		$model =& get_model('company');
		
		$query = "SELECT * FROM {$model->table}";
		
		$wheres = array();
		
		foreach($args as $column => $value){
			
			if ( empty($value) || 0 == $value ){
				continue;
			}
			
			// do we have a fancy arg?
			// e.g. "age>=21" means $column is "age>"
			// whereas "age>21" mean $column is "age>21"
			$_gte = str_endswith($column, '>');
			$_lte = str_endswith($column, '<');
			$_not = str_endswith($column, '!');
			
			if ($_lte){ // less than or equal to
				$column = str_replace('<', '', $column);
				$operator = '<=';
			} 
			elseif ( str_in($column, '<') ){ // less than
				$parts = explode('<', $column);
				$column = $parts[0];
				$value = $parts[1];
				$operator = '<';
			}
			
			elseif ($_gte){ // greater than or equal to
				$column = str_replace('>', '', $column);
				$operator = '>=';
			} 
			elseif ( str_in($column, '>') ){ // greater thano
				$parts = explode('>', $column);
				$column = $parts[0];
				$value = $parts[1];
				$operator = '>';
			}
			
			elseif ($_not){ // not like or equal to
				$column = str_remove($columne, '!');
				if ( '%s' === $model->get_column_format($column) ){
					$operator = 'NOT LIKE';
					$value = "'" . like_escape($value) . "'";
				}
				else
					$operator = '!=';
			}
			
			elseif ( '%s' === $model->get_column_format($column) ){
				$operator = 'LIKE';
				$value = "'" . like_escape($value) . "'";
			}
			
			if ( $model->is_column($column) ){
				
				$wheres[] = esc_sql($column) . ' ' . $operator . ' ' . $value;
			}
		}
		
		if ( !empty($wheres) ){
			$query .= " WHERE " . implode(" AND ", $wheres);	
		}
		
		$orderby = $model->is_column($args['orderby']) ? $args['orderby'] : 'marketcap';
		$order = $model->is_column($args['order']) ? strtoupper($args['order']) : 'DESC';
		$limit = ( isset($args['limit']) && is_numeric($args['limit']) ) ? $args['limit'] : '50';
		
		$query .= " ORDER BY $orderby $order LIMIT $limit;";
		
		$api->response->response = array_merge( array('query' => str_replace("SELECT * FROM {$model->table}", "SELECT companies", $query)), $api->response->response );
		
		$companies = $model->get_results( $query );
		
		array_walk($companies, 'addTitleToObject');
		
		$return = array();
		$key_prefix = $key_suffix = '';

		if ( $api->is_xml() ){
			$key_prefix = 'company ticker="';
			$key_suffix = '"';
		}
		else if ( !$api->is_json() ){
			return 'Invalid output format';	
		}
		
		foreach($companies as $key => $company){
			$return[ $key_prefix . $company->ticker . $key_suffix ] = $company;
		}
		
		return $return;
	}
	
	function company_reserves( $q ){
			
	}
	
	function get_reserves( $q ){
		
		$year = 'all';
		
		if ( $q )
			$year = $q;
		
		return get_companies_with_reserves( $year );	
	}
	
	
}

function addTitleToObject(&$object){
	$object->name = get_the_title($object->id);
	return $object;
}
