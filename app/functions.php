<?php
/** 
* functions.app.php
*
* Custom PHP functions.
*/

class App_Query {
	/**
	 * List of queries. A single query is an associative array:
	 * 
	 * 1. 'table' string The table being queried. Alternatively pass a Model instance.
	 *
	 * 2. 'select' string|array (optional) The columns to select.
	 *		Default: '*'
	 *
	 * 3. 'where' array (optional) Where clauses. 
	 *		Each array element is an array:
	 *			array({column}, {operator}, {value})
	 *
	 * 4. 'operator' string (optional) The relation of wheres to eachother
	 *		Possible values: 'AND', 'IN', etc.
	 *		Default: 'AND'
	 *
	 * @var array
	 */
	public $queries = array();

	public $relation;	
	
	function query( $model, $select = '*', $wheres = array(), $where_relation = 'AND' ){
		
		if ( is_string($model) )
			$model =& get_model( $model );
		
		$str = '';
		
		$table = $model->table;	
		
		$str .= "SELECT $select FROM $table";
		
		if ( !empty($wheres) ){
			
			$str .= ' WHERE ';
			
			foreach($wheres as $where){
				
				$col = $where[0];
				$operator = $where[1];
				$value = $where[2];
				
				if ( stripos($operator, 'like') !== false ){
					$value = "'" . like_escape( $value ) . "'";
				}
				elseif ( stripos($operator, 'in') !== false ){
					$value = "(" . sql_escape( $value ) . ")";	
				}
				
				$where_str .= sql_escape( $col ) . " $operator $value";
				
			}
			
			$str .= implode(" $where_relation ", $where_str);
		}
			
	}
	
}

function company_has_reserves( $id = null, $year = 'all' ){
	$id = get_company_id($id);
	
	$cachename = "company_has_reserves/{$id}";
	if ( cache_isset($cachename, 'reserves') ) 
		return cache_get($cachename, 'reserves');
	
	$has_reserves = get_model('Company_Reserves')->has_reserves($id, $year);	
	
	cache_set( "company_has_reserves/{$id}", $has_reserves, 'reserves' );
	
	return $has_reserves;
}

function get_company_reserves( $id = null, $year = 'all' ){
	$id = get_company_id($id);
	$model =& get_model('Company_Reserves');
	return $model->get_reserves($id, $year);
}

function get_company_co2_per_gj( $id = null ){
	
	$id = get_company_id($id);
	
	$model =& get_model('Company_Reserves');	
	$co2pergj = $model->get_co2_per_gj($id);
	
	return $co2pergj;
}

function get_company_co2( $id = null ){
	
	$id = get_company_id($id);
	
	$cachename = "company_co2/{$id}";
	if ( cache_isset($cachename, 'reserves') ) 
		return cache_get($cachename, 'reserves');
	
	$model =& get_model('Company_Reserves');
	$co2 = $model->get_co2($id);		
	
	cache_set($cachename, $co2, 'reserves');
	
	return $co2;
}

function get_company_reserve_years( $id = null ){
	
	$id = get_company_id($id);
	
	$cachename = "company_reserve_years/{$id}";
	if ( cache_isset($cachename, 'reserves') ) 
		return cache_get($cachename, 'reserves');
	
	$years = get_model('Company_Reserves')->get_reserve_years($id);
	
	cache_set($cachename, $years, 'reserves');
	
	return $years;
}

function get_unique_reserve_types( &$reserves, &$types = array() ){
	foreach($reserves as $year => &$reserve){
		foreach( $reserve->get_type_labels() as $type ){
			if ( !in_array($type, $types) )
				$types[] = $type;
		}
	}
	return $types;
}

function get_company_country( $id = null ){
	$id = get_company_id($id);
	
	if ( empty($id) ) return false;
	
	$cachename = "company_country/{$id}";
	if ( cache_isset($cachename, 'company') ) 
		return cache_get($cachename, 'company');
	
	$model =& get_model('Company');
	$country = $model->get_var("SELECT country from `{$model->table}` WHERE `id` = $id");
	
	cache_set($cachename, $country, 'company');
	
	return $country;
}

function get_company_marketcap( $id = null, $label = false, $suffix = true, $no_update = false ){
	
	$r = '';
	$id = get_company_id($id);
	
	$ticker = get_company_ticker($id, 'yahoo');
	$model =& get_meta_model('company');
	$marketcap = $model->get_object( $id, 'marketcap' );
	
	if ( $label ){
		if ( !is_string($label) ) 
			$label = 'Market Cap: ';
		$r .= text_label($label);
	}
	
	if ( (empty($marketcap) || $marketcap->is_expired()) ){
		if ( $no_update ) 
			return false; // update separately
		$r .= ajax_html(array(
			'id' => 'marketcap-' . $ticker,
			'class' => 'ajax-request-ready',
			'action' => 'update_company_marketcap',
			'q' => $ticker,
		));
	}
	else $r .= $marketcap->meta_value;
	
	if ($suffix) $r .= ' B';
	
	return $r;
}


function get_companies_with_reserves( $year = 'most-recent', $order_by = 'marketcap', $order = 'DESC' ){
	
	global $wpdb;
	
	$company_model =& get_model('company');
	$reserve_model =& get_model('company_reserves');
	
	$company_table = $company_model->table;
	$reserve_table = $reserve_model->table;
	
	$query = "SELECT * FROM $company_table INNER JOIN {$reserve_table} 
		ON {$company_table}.id = {$reserve_table}.post_id";
	
	if ( 'most-recent' === $year )
		$year = date('Y') - 1;
	
	if ( 'all' !== $year )
		$query .= " WHERE {$reserve_table}.year = $year";
	
	$query .= " ORDER BY {$company_table}.{$order_by} $order";
	
	return $wpdb->get_results( $query );
}


function get_company_ticker( $id = null, $format = 'normal' ){
	
	$id = get_company_id($id);
	
	$cachename = "ticker_from_id/{$format}/{$id}";
	if ( cache_isset($cachename, 'company') ) 
		return cache_get($cachename, 'company');
	
	$company = get_postx($id);
	$ticker = format_ticker( $company->ticker, $format );
	
	cache_set( "ticker_from_id/{$format}/{$id}", $ticker, 'company' );
	cache_set( "id_from_ticker/{$format}/{$ticker}", $id, 'company');
	
	return $ticker;
}

function format_ticker($ticker, $format = 'normal'){
	switch($format){
		case 'normal':
		case 'msn':
		case 'google':
		case null:
		default: return str_replace(array('-','/'), '.', $ticker);
		case 'yahoo': return str_replace(array('.','/'), '-', $ticker);
		case 'sec':	return str_replace(array('.','/','-'), '', $ticker);
	}
}

function get_company_by_ticker( $ticker ){
	
	$model =& get_model('company');
	
	$company = $model->query_by( 'ticker', format_ticker($ticker, 'normal') );
	
	return $company;
}

function get_company_id_from_ticker( $ticker, $format = 'normal' ){
	
	$ticker = format_ticker($ticker, $format);
	
	if ( cache_isset("id_from_ticker/{$format}/{$ticker}", 'company') ) 
		return cache_get( "id_from_ticker/{$format}/{$ticker}", 'company' );
	
	$model =& get_model('company');
	
	$id = $model->get_primary_key_where( array( 'ticker' => $ticker ) );
	
	cache_set( "id_from_ticker/{$format}/{$ticker}", $id, 'company' );
	cache_set( "ticker_from_id/{$format}/{$id}", $ticker, 'company' );
	
	return $id;
}

function get_company_id( $id = null ){
	
	if ( is_numeric($id) ){
		return $id;
	}
	
	else if ( is_object($id) ){	
		if ( $id instanceof Postx_Object )
			return $id->id;
		if ( $id instanceof Meta_Object )
			return $id->post_id;
		if ( $id instanceof WP_Post )
			return $id->ID;
	}
	
	else if ( null === $id ){
		global $post;
		return $post->ID;	
	}
	
	else if ( is_array($id) ){	
		$_checks = array(
			'id', 'ID', 'post_id', 'post_ID', 'company_id', 'object_id', 'object_ID',
		);
		foreach($_checks as $var){
			if ( isset($id[$var]) )
				return $id[$var];	
		}
	}
	return null;
}
