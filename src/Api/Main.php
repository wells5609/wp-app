<?php

class Api_Main {
	
	public $query;
	
	public $response;
	
	public $router;
	
	public $_running = false;
	
	public $is_ajax = false;
	
	protected $ajax_action_prefix = 'wp_ajax_';
	
	protected $ajax_nopriv_action_prefix = 'wp_ajax_nopriv_';
	
	static protected $_instance;
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	protected function __construct(){
	
		$this->query = new Api_Query();
		$this->response = new Api_Response();
		$this->router = new Api_Router();
	
		register_datatype( 'api_auth' );
	}


	public function add_route( $route, $callable, $http_method = '', $pos = 'top' ) {
		$this->router->add_route($route, $callable, $http_method, $pos);
		return $this;
	}
	
	public function add_query_var( $name, $regex ){
		$this->router->add_query_var( $name, $regex );
		return $this;	
	}
	
	
	public function is_json(){
		if ( isset($this->response->content_type) && ( 'json' === $this->response->content_type || 'jsonp' === $this->response->content_type ) )
			return true;
		return false;
	}
	
	public function is_xml(){
		if ( isset($this->response->content_type) && ('xml' === $this->response->content_type) )
			return true;
		return false;
	}
	
	
	public function respond(){
		
		if ( !is_callable( $this->callback ) ){
			$this->error('Unknown method');
		}
		
		$this->query->import( $this->_query_vars ); // vars set by router
		
		$results = call_user_func_array( $this->callback, array($this->query->get_vars()) );
		
		$response = $this->response->build( $results );
		
		if ( $this->is_json() )
			$response = $this->to_json($response);
		
		elseif ( $this->is_xml() )
			$response = $this->to_xml($response);
		
		die( $response );
	}
	
	public function error( $message = 'Error', $error_type = 'default' ){
		
		switch ( $error_type ) {
			case 'auth':
				$status_header = 403;
				break;
			case 'default':
			default:
				$status_header = 400;
				break;
		}
		
		$response = $this->response->build( false, $message, $status_header );
		
		if ( $this->is_json() )
			$response = $this->to_json($response);	
		
		if ( $this->is_xml() )
			$response = $this->to_xml($response);
		
		die( $response );
	}
		
	public function to_json( $response, $callback = null ){
	
		$json = json_encode( $response, JSON_FORCE_OBJECT );
		
		if ( isset($this->query->callback) )
			$callback = $this->query->callback;
		
		if ( !empty($callback) )
			$json = $callback . '(' . $json . ')';
		
		return $json;
	}
	
	public function to_xml( $response ){
		
		if ( !is_array($response) ) $response = (array) $response;
		
		return xml_start('1.0') . xml( $response );
	}
	
	
	public function ajax(){
		global $api;
		
		$this->is_ajax = true;
		ini_set( 'html_errors', 0 );
		
		if ( AJAX_FORCE_NONCE && !ajax_verify_nonce() )
			wp_die( 'Security check didn´t pass, please.', API_AJAX_VAR );
		
		$this->response->headers(200, false, true);
		
		if ( is_user_logged_in() )
			do_action( $this->ajax_action_prefix . $this->query->action, $this->query->get_vars() );
		else
			do_action( $this->ajax_nopriv_action_prefix . $this->query->action, $this->query->get_vars() );			
		
		wp_die( 'Your ' . API_AJAX_VAR . ' call does not exists or exit is missing in action!', API_AJAX_VAR );
		
		exit;	
	}
	
}
