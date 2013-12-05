<?php

class Api_Response {
	
	public $response = array(
		'status' => null,
	);
	
	public $content_type;
	
	public $content_types = array(
		'html' => 'text/html; charset=utf-8',
		'json' => 'application/json; charset=utf-8',
		'jsonp' => 'text/javascript; charset=utf-8',
		'xml' => 'text/xml; charset=utf-8',
	);
	
	public $default_content_type = 'json';
	
	public $default_ajax_content_type = 'html';
	
	
	public function set_content_type( $type, $force = false ){
		if ( isset($this->content_types[$type]) && ( !isset($this->content_type) || $force ) ){
			$this->content_type = $type;
			return true;
		}
		return false;
	}
	
	public function build( $results = null, $message = null, $header_status = null, $cache = false ){
		global $api;
		
		$status = empty($results) ? false : true;
		
		$this->response['status'] = $status ? 'ok' : 'error';
		
		if ( !empty($message) )
			$this->response['message'] = $message;
		elseif ( !$status )
			$this->response['message'] = 'Error';
				
		if ( is_array($results) )
			$this->response['count'] = count($results);
		
		if ( defined('WP_DEBUG') && WP_DEBUG ){
			$this->response['time'] = timer_stop(0, 3) . ' s';
			$this->response['queries'] = get_num_queries();
			$this->response['memory'] = round(memory_get_peak_usage()/1024/1024, 3) . ' MB';
		}
				
		if ( $status ){
			$this->response['results'] = $results;
			$header_status = 200;
		}
		elseif ( !$header_status ){
			$header_status = 400;
		}
		
		$this->headers( $header_status, $cache );
				
		return array( 'response' => $this->response );
	}
	
	public function headers( $status, $cache = null, $no_sniff = true ){
		global $api;
		
		status_header( $status );
		
		if ( isset($this->content_types[$this->content_type]) ){
			$content_type = $this->content_types[$this->content_type];
		}
		else {
			$content_type = $this->get_default_content_type();
		}
		
		header("Content-Type: $content_type");
		
		if ( false === $cache )
			nocache_headers();
		
		if ( true === $no_sniff )
			send_nosniff_header();
	}
	
	
	protected function get_default_content_type(){
		global $api;
		if ( $api->is_ajax )
			return $this->content_types[$this->default_ajax_content_type];
		return $this->content_types[$this->default_content_type];
	}
	
}