<?php

class Api_Router {
	
	protected $routes;
	
	protected $_routes_top = array();
	protected $_routes_bottom = array();
	
	protected $api_query_vars = array(
		'dir'		=> '([^_/][^/]+)',
		'path'		=> '(.+?)',
		'id'		=> '(\d+)',
		'q'			=> '([\w][\w\.*]+)',
		's'			=> '(.?.+?)',
	);
	
	
	function __construct(){
		
		$this->routesInit();
		
		add_action( 'parse_request', array($this, 'route'), 0 );
	}
	
	// @callback parse_request
	function route( ) {
		
		$this->routes = apply_filters( 'api_routes', $this->getRoutes() );
	
		if ( $this->matchRoute() ){
			
			global $api;
			
			ini_set('html_errors', 0);
			
			$api->respond(); // send api response
		}
	}
	
	public function add_routes( array $routes, $pos = 'top' ){
		foreach($routes as $route => $cb ){
			$this->add_route($route, $cb, '', $pos);
		}
	
	}
		
	public function add_route( $route, $callable, $method = '', $pos = 'top' ) {
		
		if ( !empty($method) )
			$route = strtoupper($method) . ':' . $route;
		else
			$route = '/' . ltrim($route, '/');
		
		if ( 'top' === $pos )
			$this->_routes_top[ $route ] = $callable;
		elseif ( 'bottom' === $pos )
			$this->_routes_bottom[ $route ] = $callable;
		else
			$this->routes[ $route ] = $callable;
		
		return $this;
	}
	
	public function add_query_var( $name, $regex ){
		if ( isset($this->api_query_vars[ $regex ]) ){
			// regex is reference to another
			$regex = $this->api_query_vars[ $regex ];	
		}
		$this->api_query_vars[ $name ] = $regex;
		return $this;	
	}
	
	public function set_base( $base ){
		$this->base = trim($base, '/\\');
		return $this;	
	}
	
	
	function add_group( $name, $priority = 10, $add_to_top = true ){
	
		if ( !isset($this->groups[$priority]) ){
			$this->groups[$priority] = array( $name );
			return;
		}
		
		if ( $add_to_top )
			$this->groups[$priority] = array_unshift( $this->groups[$priority], $name );
		else
			$this->groups[$priority] = array_push( $this->groups[$priority], $name );
	}
	
	function matchRouteGroup(){
		
		ksort($this->groups);
		
		foreach($this->groups as $priority => $names){
		
			foreach($names as $name){
		
				if ( str_startswith($__current_route_name__, $this->base . '/' . $name) ){
					
					$this->_matches['group'] = $name;
					return true;
				}
			}
		}
		return false;
	}
	
	function groupMatchRoute(){
		
		$this->set_base('api');
		$this->add_route_group( 'post', 5 );
		$this->add_route_group( 'ajax', 3 );
		$this->add_route_group( 'dynamic', 15 );
		
		
		$this->group_routes['post'] = array(
			'update/:id/:q'		=> array('Api_Post_Controller', 'update'),
			'get/:id'			=> array('Api_Post_Controller', 'get'),
			'new/:id/:q'		=> array('Api_Post_Controller', 'insert'),
			':id/:q'			=> array('Api_Post_Controller', 'update'),
			':id'				=> array('Api_Post_Controller', 'get'),
		);
		
		$this->group_routes['ajax'] = array(
			':action/:s(extra)'	=> array(__CLASS__, 'map_ajax'),
			':action'			=> array(__CLASS__, 'map_ajax'),
		);
		
		foreach( $this->groups as $group => $vars ){
			
			if ( empty($this->group_routes[$group]) ) continue;
			
			$priority = isset($vars['priority']) ? (int) $vars['priority'] : 10;
			$slug = isset($vars['slug']) ? $vars['slug'] : $group;
			
			if ( !isset($this->_routes[$priority]) ){
				
				$this->_routes[$priority] = $this->group_routes[$group];
			}
			else {
				
				$this->_routes[$priority] = array_merge( $this->_routes[$priority], $this->group_routes[$group] );
			}
			
		}
		
		/////
		
		$group = $this->_matches['group'];
		
		$query_vars = implode('|', array_keys($this->api_query_vars) );
		
		foreach($this->group_routes[$group] as $route => $callback){
			
			// Match query vars to replace
			preg_match_all('/(:{1}(' . $query_vars . ')\((\w+)\)?)/', $route, $matches);
			
			$translations = array_combine( $matches[3], $matches[2] );
			
			foreach($translation as $friendly_key => $regex_key){
				
				$regex = $this->api_query_vars[$regex_key];
				
				$route = str_replace( ':' . $regex_key . '(' . $friendly_key . ')', $regex, $route);
				
				$var_keys[] = $friendly_key;
			}
			
			
			$route = $this->base . '/' . $group . '/' . $route;
			
			$uri = '/' . $api->query->request_uri; // add back slash to match routes
			
			$var_keys = $this->replaceRegex($route); // $var_keys = Keys of regex-matched query vars
			
			if ( !str_startswith($route, '/') ){
				$uri = $api->query->request_method . ':' . $uri;
			}
			
			if ( preg_match('#^/?' . $route . '/?$#', $uri, $matches ) ) {
				
				
			}

		}
			
	}
		
	protected function routesInit(){
		
		$this->_routes_top = array();
		
		$this->_routes_bottom = array(
			
			'/api/:dir(controller)/:dir(method)/:id/:s(extra)'	=> array(__CLASS__, 'map_callback'),
			'/api/:dir(controller)/:dir(method)/:q/:s(extra)'	=> array(__CLASS__, 'map_callback'),
			
			'/api/:dir(controller)/:dir(method)/:id'			=> array(__CLASS__, 'map_callback'),
			'/api/:dir(controller)/:dir(method)/:q'				=> array(__CLASS__, 'map_callback'),
			'/api/:dir(controller)/:dir(method)/:s(extra)'		=> array(__CLASS__, 'map_callback'),
			
			'/api/:dir(controller)/:id/:s(extra)'				=> array(__CLASS__, 'map_callback'),
			'/api/:dir(controller)/:q/:s(extra)'				=> array(__CLASS__, 'map_callback'),
			
			'/api/:dir(controller)/:dir(method)'				=> array(__CLASS__, 'map_callback'),
			'/api/:dir(controller)/:s(extra)'					=> array(__CLASS__, 'map_callback'),
		);
		
		$this->routes = array(
			
			'/api/post/update/:id/:q'				=> array('Api_Post_Controller', 'update'),
			'/api/post/get/:id'						=> array('Api_Post_Controller', 'get'),
			'POST:/api/post/new/:id/:q'				=> array('Api_Post_Controller', 'insert'),
			'POST:/api/post/:id/:q'					=> array('Api_Post_Controller', 'update'),
			'GET:/api/post/:id'						=> array('Api_Post_Controller', 'get'),
			
			'/api/ajax/:dir(action)/:s(extra)'		=> array(__CLASS__, 'map_ajax'),
			'/api/ajax/:dir(action)'				=> array(__CLASS__, 'map_ajax'),
		);
		
		return true;	
	}
	
	protected function getRoutes(){
		// put more specific routes up top
		return array_merge( $this->_routes_top, $this->routes, $this->_routes_bottom );
	}
	
	protected function matchRoute(){
		
		global $api;
		
		foreach($this->routes as $route => $callback){
			
			$uri = '/' . $api->query->request_uri; // add back slash to match routes
			
			$var_keys = $this->replaceRegex($route); // $var_keys = Keys of regex-matched query vars
			
			if ( strpos($route, '/') !== 0 ){
				// route does not begin with slash => has http method
				$uri = $api->query->request_method . ':' . $uri;
			}
			
			if ( preg_match('#^/?' . $route . '/?$#', $uri, $matches ) ) {
				unset($matches[0]); // remove full match
				
				$api->callback = $callback;
				$api->_matched_route = $route;
				$api->_query_vars = array_combine( $var_keys, $matches ); // join var keys and values
					
				return true;
			}
			unset($uri);
		}
		return false;
	}
	
	protected function replaceRegex(&$route){
		$args = array();
		
		foreach($this->api_query_vars as $var => $regex){
			
			// Match query vars with renamings - e.g. :id(post_id)
			preg_match_all('/(:{1}(' . $var . ')\((\w+)\)?)/', $route, $matches);
			
			if ( isset($matches[3]) && !empty($matches[3]) ){
	
				$translations = array_combine( $matches[3], $matches[2] );
				
				foreach($translations as $friendly_key => $regex_key){
					
					$route = str_replace(':'.$regex_key.'('.$friendly_key.')', $this->api_query_vars[$regex_key], $route);
					$args[] = $friendly_key;
				}
			}
			
			// have non-(re)named var
			if ( strpos($route, ':' . $var) !== false ){
			
				if (isset($this->api_query_vars[ ltrim($regex,':') ]) ){
					// regex is a reference to another
					$regex = $this->api_query_vars[ ltrim($regex,':') ];
				}
				$route = str_replace( ':' . $var, $regex, $route );
				$args[] = $var;
			}
		}
		return $args;
	}
	
	
	// static
	function map_ajax( $args ){
		global $api;
		
		define( 'DOING_AJAX', true );
		
		do_action('load_ajax_handlers', $args);
		
		$api->ajax(true);
	}
	
	// static
	function map_callback( $args ){
		global $api;
		
		$controller = ucfirst($api->query->controller) . '_ApiController';
		$method = $api->query->method;
		$q = isset($api->query->q) ? $api->query->q : null;
	
		if ( !is_callable( array($controller, $method) ) )
			$api->error('Unknown method');
		
		$controller = call_user_func( array($controller, 'instance') );
		
		return $controller->$method( $q );
	}
		
}