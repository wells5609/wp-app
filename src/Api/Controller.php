<?php

abstract class Api_Controller {
	
	
	public $methods_require_apikey = array();
	
	public $_last_auth_message;
	
	
	abstract static function instance();
	
	
	public function validate_apikey_if_required( $method, $add_message_to_response = true ){
		
		global $api;
		
		if ( !$this->method_requires_apikey($method) )
			return true;
		
		if ( !$this->validate_apikey() ){
			
			$api->error('Unauthorized access - method requires valid API key.', 'auth');
		}
		
		if ( $add_message_to_response ){
			$api->response->response = array_merge( $this->_last_auth_message, $api->response->response );	
		}
		return true;
	}
	
	
	public function method_requires_apikey( $method ){
		
		return ( true === $method || in_array($method, $this->methods_require_apikey) ) ? true : false;
	}
	
	
	public function validate_apikey(){
		global $api;
		
		if ( !isset($api->query->apikey) ){
			return false;
		}
		
		$apikey = $api->query->apikey;
		$auth_model = get_model('Api_Auth');
		
		$response = $auth_model->do_api_request($apikey);	
		
		$this->_last_auth_message = $response;
	
		if ( !is_array($response) )
			return false;	
		
		return true;
	}
	
	
	public function get_last_auth_message(){
		
		return $this->_last_auth_message;	
	}
	
}