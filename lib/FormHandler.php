<?php

class FormHandler {
	
	public $msgs		= array();
	
	public $is_error	= false;
	
	public $errors		= array();
	
	public $method;
	
	public $success;
	
	
	/**
	* The raw form data
	*/
		protected $_form_data;
	
	
	/**
	* Whether to sanitize the form data
	*/
		protected $sanitize_args = true;
	
	/**
	* Methods to run during validation
	*
	* Methods should set $errors if error, otherwise do nothing
	*/
		protected $validations = array(
			'validate_honey',
			'validate_human',
			'validate_nonce',
		);
	
	
	/**
	* Methods to run before processing.
	* Generally used to set object vars used in processing
	*/
		protected $pre_processes = array();
	
	
	/**
	* Methods to run during processing.
	* e.g. insert a post, save terms, etc.
	*/
		protected $processes = array();
	
	
		protected $on_complete = array();
	
	/**
	* HTTP methods to accept
	*/
		protected $_valid_methods = array(
			'GET'	=>	'GET', 
			'POST'	=>	'POST'
		);	
	
	
	/**
	* vars used in validations
	* Treat as constants
	*/
		protected $HUMAN_CHECK = 'bad';
		protected $NONCE_NAME = 'form-handler-nonce';
	
	
	/**
	* Methods that have been run
	* For debugging
	*/
		protected $_methods_run = array();
	
	
	/** Vars used for certain processes */


	/**
	* var used for insert_wp_post() processing method
	*/
		protected $wp_post = array();
	
	/** 
	* The inserted Post ID (or whichever)
	*/
		protected $post_id;
	
	/**
	* var used for set_post_terms() processing method
	* Requires a $post_id var
	* array of taxonomy => term
	*/
		protected $post_terms = array();
	
	/**
	* used in wp_insert_term()
	*/
		protected $_append_post_terms = false; 
	
	
	/**
	* Validates method, sets up data, and initiates processing
	* 
	* Will set a var with method's name - e.g. $this->POST, $this->GET
	* Also sets generic $_form_data var
	*/
	final function __construct( $args, $method = 'POST' ){
		
		$method = strtoupper($method);
		
		if ( isset($this->_valid_methods[$method]) ){
			
			$this->method = $method;
			
			//if ( $this->sanitize_args )
				//$this->sanitize($args);
			
			$this->$method = $this->_form_data = $args;
		}
		else {
			throw new InvalidArgumentException($method . ' is not a permitted HTTP method.');	
		}
		
		$this->init();
		
		$this->process();
	}
	
	
	function is_ajax(){
		return (
			!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
			|| defined("DOING_AJAX") && DOING_AJAX
		);
	}
	
	
	// Sets up stuff for handler
	protected function init(){
		// for child classes
	}
	
	public function is_error(){
		return $this->is_error;	
	}
	
	public function get_errors(){
		return $this->errors;
	}
	
	public function get_messages(){
		return $this->msgs;	
	}
	
	public function field_is_error( $name ){
		return isset($this->errors[$name]);
	}
	
	public function get_error_message( $arg ){
		if ( isset($this->errors[$arg]) )
			return $this->errors[$arg];	
		return '';
	}
	
	public function get_message($arg){
		if ( isset($this->msgs[$arg]) )
			return $this->msgs[$arg];
		elseif ( isset($this->errors[$arg]) )
			return $this->errors[$arg];
		return '';
	}
	
	// Sends error messages - should only be called once
	protected function send_error($messages = array()){
		
		if ( !empty($messages) )
			array_merge($this->msgs, $messages);
		
		return array('success' => false, 'messages' => $this->msgs);	
	}
	
	
	/** 
	* Processes the form	
	*
	* First does validations and exits if error
	* If no errors, does pre_processes and processes
	* Finally sets $success = true
	*/
	final private function process(){
		
		if ( ! $this->validate() )
			return $this->send_error();
		
		foreach($this->pre_processes as $pre_process){
			
			$this->$pre_process();
		}
		
		foreach($this->processes as $process){
			
			$this->runMethod($process);
		}
		
		$this->success = true;
		
		if ( !empty($this->on_complete) ){
			foreach($this->on_complete as $complete){
				$this->runMethod($complete);	
			}	
		}
		
	}
	
	/**
	* Runs a method
	* Calls its pre_*() method prior to running
	* Adds method to $_methods_run for debugging
	*/
	final private function runMethod( $method ){
		
		if ( is_callable(array($this, $method)) ){
			
			if ( is_callable( array($this, 'pre_' . $method) ) ){
				$this->{'pre_'.$method}();
				$this->_methods_run[ 'pre_'.$method ] = 'pre_'.$method;
			}
			
			$this->$method();
			
			$this->_methods_run[ $method ] = $method;
		}
	}
	
	
	// Runs $validations and returns false if error
	final private function validate(){				
		
		foreach($this->validations as $method)
			$this->$method();
		
		if ( !empty($this->errors) ){
			$this->is_error = true;
			return false;	
		}
		
		return true;
	}
	
	
	function validate_honey(){
		if ( !isset($this->POST['honey']) || '' !== $this->POST['honey'] ){
			$this->errors['honey'] = 'Asshole.';
		}
	}
	function validate_human(){
		if ( empty($this->POST['human_check']) || $this->HUMAN_CHECK !== strtolower($this->POST['human_check']) ){
			$this->errors['human'] = 'You must be human to fill out this form.';
		}	
	}
	function validate_nonce(){
		if ( ! wp_verify_nonce( $this->POST['nonce'], $this->NONCE_NAME) ) {
			$this->errors['nonce'] = 'Wise guy, huh?';
		}
	}
	
	
	public function display_messages($button = true){
		
		if ( $this->is_error() ){
			foreach($this->get_errors() as $type => $msg){
				echo '<div class="alert alert-danger"><button type="button" class="close" aria-hidden="true" data-dismiss="alert">&times;</button><h4>Error</h4><p>' . $msg . '</p>';
				if ( $button ) echo '<button type="button" class="btn btn-danger" data-dismiss="alert">Ok</button>';
				echo '</div>';
			}
		}
		else if ( $this->success ){
			foreach($this->msgs as $message){
				echo '<div class="alert alert-success"><button type="button" class="close" aria-hidden="true" data-dismiss="alert">&times;</button><p>' . $message . '</p>';
				if ( $button ) echo '<button type="button" class="btn btn-success" data-dismiss="alert">Ok</button>';
				echo '</div>';	
			}
		}	
	}
	
	public function display_messages_if_ajax(){
		if ( $this->is_ajax() ){
			die( $this->display_messages() );
		}
	}
	
	
	// Run before insert_wp_post();
	protected function set_wp_post_for_insert(){}
	
	// insert a wp_post
	private function insert_wp_post(){
		
		$this->set_wp_post_for_insert();
		
		if ( !isset($this->wp_post) ){
			throw new Exception('var "wp_post" not set - cannot run FormHandler::insert_wp_post()');
		}
		
		$this->post_id = wp_insert_post($this->wp_post);
		
		if ( 0 !== $this->post_id ){
			$this->msgs[] = '<h4>Success!</h4>'
				. get_the_title($this->post_id) . " was added to the database."
				. ' <a class="alert-link" href="' . esc_url(get_permalink($this->post_id)) . '">View &raquo;</a>';
		}
		
		return $this->post_id;
	}
	
	// set the post terms using $post_id
	private function set_post_terms(){
		
		if ( !isset($this->post_id) ){ 
			if ( in_array('insert_wp_post', $this->processes) )
				$this->runMethod('insert_wp_post');
			elseif ( method_exists($this, 'set_post_id') )
				$this->runMethod('set_post_id');
			else {
				throw new Exception('Ran set_post_terms() too early. No post_id set');
			}
		}
		
		foreach($this->post_terms as $taxonomy => $terms){
			
			if ( !is_array($terms) )
				$terms = array($terms);
			
			wp_set_post_terms($this->post_id, $terms, $taxonomy, $this->_append_post_terms);
		}
	}
	
	/** Creates a term (in an existing taxonomy) if it does not exist
	*	
	*	returns array( 
	*		'success' => boolean - true if term was added, 
	*		'term_id' => the existing or new term_id
	*	);
	*/
	final function create_term_if_not_exists($term, $taxonomy, $args = array()){
		
		if ( !taxonomy_exists($taxonomy) )
			return false;
		
		if ( !is_taxonomy_hierarchical($taxonomy) )
			$term = strval($term);
		
		$args = array_merge(array('parent' => 0), $args);
		
		$exists = term_exists( $term, $taxonomy, $args['parent'] );
		
		if ( is_array($exists) ) {
			// term exists
			$success = false;
			$term_id = (int) $exists['term_id'];
		}
		else {
			// Create
			$new_term = wp_insert_term( $term, $taxonomy, $args );
						
			if ( is_wp_error($new_term) )
				$success = false;
			else {
				$success = true;
				$term_id = (int) $new_term['term_id'];
				$this->msgs[] = "Created new {$taxonomy} {$term}";
			}
		}
		
		return array('success' => $success, 'term_id' => $term_id);
	}	
	
}