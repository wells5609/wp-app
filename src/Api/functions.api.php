<?php

function api_add_query_var( $name, $regex ){
	global $api;
	$api->add_query_var( $name, $regex );	
	return;
}

function api_add_route( $route, $func, $http_method = '' ){
	global $api;
	$api->add_route( $route, $func, $http_method );
	return;
}

function api_is_json(){
	global $api;
	return $api->is_json();	
}

function api_get_user_apikey( $user_id = null ){
	if ( null === $user_id )
		$user_id = get_current_user_ID();
	return api_get_apikey_by( 'user_id', (int) $user_id );
}

function api_get_apikey_by( $field, $value ){
	$model =& get_model('Api_Auth');	
	$result = $model->get_apikey_by($field, $value);
	return $result;
}