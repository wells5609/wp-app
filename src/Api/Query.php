<?php
class Api_Query {
	
	public $request_method;
	
	public $request_uri;
	
	public $query_string;
	
	public $callback; // jsonp callback
	
	public $args = array(); // for controller
	
	protected $force_as_property = array(
		'apikey',
		'q',
		'callback',
	); // strings of variables to force from $args to object property
	
	static protected $_setup = false;
	
	
	public function __construct(){
		
		$request_uri = $_SERVER['REQUEST_URI'];
		$query_string =  $_SERVER['QUERY_STRING'];
		
		if ( !empty($query_string) )
			$request_uri = str_replace('?' . $query_string, '', $request_uri);
		
		$this->set( 'request_method', strtoupper($_SERVER['REQUEST_METHOD']) );
		$this->set( 'request_uri', $this->filterUriComponent($request_uri) );
		$this->set( 'query_string', $this->filterUriComponent($query_string) );		
	}
	
	public function import( array $vars, $as_args = false ){
		global $api;
		foreach($vars as $var => $val){
			
			$var = str_replace('amp;', '', $var); // TODO: encoding issue, probably parse_str
			
			// match output type file extensions appended to vars
			// e.g. "../api/method/param.xml" means $val is "param.xml"
			preg_match("/[\.](html|xml|jsonp|json)/", $val, $matches);
			
			if ( isset($matches[1]) ){
				$api->response->set_content_type($matches[1]);
				$val = str_replace('.'.$matches[1], '', $val);
			}
			
			$val = wp_filter_kses($val);
			
			if ( $as_args ){
				$this->setArg( $var, $val );
			}
			else {
				$this->set( $var, $val );	
			}
		}
		if ( !self::$_setup ) {
			self::$_setup = true;
			$this->setup();
		}
	}
	
	public function get_vars( $output = ARRAY_A ){
		$vars = get_object_vars( $this );
		if ( OBJECT === $output )
			$vars = (object) $vars;
		else if ( ARRAY_N === $output )
			$vars = array_values( $vars );	
		return $vars;
	}
		
	public function get($var){
		return isset($this->$var) ? $this->$var : null;	
	}
	
	public function set($var, $val){
		$this->$var = $val;
		return $this;
	}
	
	
	protected function filterUriComponent( $str ){
		return trim( wp_filter_nohtml_kses($str), '/' );	
	}
		
	protected function setup(){
		global $api;
		
		if ( strpos($this->request_uri, '.') !== false ){
			// file extension at end of $request_uri (stripped of query str)
			$pieces = explode('.', $this->request_uri);
			$ext = array_pop($pieces);
			if ( isset($api->response->content_types[ $ext ]) ){
				$api->response->set_content_type($ext);
				$this->request_uri = str_replace('.' . $ext, '', $this->request_uri);
			}	
		}
		
		if ( !empty($this->query_string) ){
		
			parse_str( $this->query_string, $query );
			
			if ( isset($query['output']) && $this->maybe_set_content_type($query['output']) )
				unset($query['output']);
			
			if ( !empty($query) )
				$this->import($query, true);
		}
		
		if ( isset($this->extra) ){
			$this->parseStringToArgs($this->extra);
		}
		
		$this->mapVars(); // translate var keys
	}
	
	protected function mapVars(){
		global $api;
		
		$map = apply_filters( "api/map_vars", array( 'extra' => 'parameters' ) );
		if ( empty($map) )
			return;
		
		// definitely a better way to do this
		foreach($map as $old => $new){
			if ( isset($this->$old) ){
				$value = $this->$old;
				unset($this->$old);
				$this->$new = $value;
			}	
		}
	}
	
	protected function parseStringToArgs( $path ){
		
		if ( strpos($path, '/') !== false ){
			$extras = explode('/', $path);
			foreach($extras as $extra){
				if ( !$this->maybe_set_content_type($extra) )
					$this->setArg(false, $extra);
			}		
		}
		elseif ( !$this->maybe_set_content_type($path) ){
			$this->setArg(false, $path);
		}
		
		unset( $path );	
	}
	
	protected function setArg( $var, $val ){
		
		if ( $var ){
			if ( in_array($var, $this->force_as_property) )
				$this->$var = $val;
			else
				$this->args[$var] = $val;
		}
		else
			$this->args[] = $val;
		
	}
	
	protected function maybe_set_content_type( &$arg ){
		global $api;
		
		if ( $api->response->set_content_type($arg) )
			return true;
		
		if ( strpos($arg, '.') !== false ){
			
			$pos = strpos($arg, '.');
			$ct = substr($arg, $pos + 1);
			
			if ( isset($api->response->content_types[ $ct ]) ){
				$api->response->content_type = $ct;
				$arg = substr($arg, 0, $pos);
			}
		}
		return false;
	}
}