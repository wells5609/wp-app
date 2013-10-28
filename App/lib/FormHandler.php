<?php
class FormHandler {
	
	public $method;
	
	public $msgs = array();
	
	public $is_error = false;
	
	public $errors = array();
	
	public $success;
	
	protected $send_error_if = array();
	
	protected $validations = array(
		'validate_honey',
		'validate_human',
		'validate_nonce',
	);
	
	protected $pre_processes = array();
	
	protected $processes = array();
	
	protected $_valid_methods = array(
		'GET'=>'GET', 
		'POST'=>'POST'
	);
	
	// Treat these like constants
	protected $HUMAN_CHECK = 'bad';
	protected $NONCE_NAME = 'add-company-nonce';

	
	function __construct( $args, $method = 'POST' ){
		
		$method = strtoupper($method);
		
		if ( isset($this->_valid_methods[$method]) ){
			$this->$method = $args;
		}
		
		$this->setup();
		
		$this->process();
	}
	
	protected function setup(){
	}
		
	private function process(){
		
		if ( !$this->validate() )
			return $this->send_error();
		
		foreach($this->send_error_if as $method){
			if ( $this->$method() ){
				$this->is_error = true;
				return $this->send_error();	
			}
		}
		
		foreach($this->pre_processes as $pre_process){
			$this->$pre_process();
		}
		
		foreach($this->processes as $process){
			$this->$process();
		}
		
		$this->success = true;
		
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
		if ( isset($this->errors[$arg]) ){
			return $this->errors[$arg];	
		}
		return '';
	}
	
	public function get_message($arg){
		if ( isset($this->msgs[$arg]) ){
			return $this->msgs[$arg];	
		}
		if ( isset($this->errors[$arg]) ){
			return $this->errors[$arg];	
		}
		return '';
	}
	
	protected function send_error($messages = array()){
		if ( !empty($messages) )
			array_merge($this->msgs, $messages);
		return array('success' => false, 'messages' => $this->msgs);	
	}
	
	private function validate(){				
		
		foreach($this->validations as $method){
			$this->$method();
		}
		
		if ( !empty($this->errors) ){
			$this->is_error = true;
			return false;	
		}
		return true;
	}
	
	protected function validate_honey(){
		if ( !isset($this->POST['honey']) || '' !== $this->POST['honey'] ){
			$this->errors['honey'] = 'Asshole.';
		}
	}
	protected function validate_human(){
		if ( empty($this->POST['human_check']) || $this->$HUMAN_CHECK !== strtolower($this->POST['human_check']) ){
			$this->errors['human'] = 'You must be human to fill out this form.';
		}	
	}
	protected function validate_nonce(){
		if ( ! wp_verify_nonce( $this->POST['nonce'], $this->$NONCE_NAME) ) {
			$this->errors['nonce'] = 'Wise guy, huh?';
		}
	}
	
	
	public function create_term_if_not_exists($term, $taxonomy, $args = array()){
		
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