<?php
		
define('API_DIGEST_KEY', 'UwHg`#sK.zSBLs.Dr9KgYaSw@_H@;A`f;:z+^NprX.9/-KqOy!7*ke`/tZR&s-_m');

class Api_Auth_Model extends Model {
	
	public $table_basename = 'api_auth';
	
	public $columns = array(
		'aid' 					=> "bigint(20) NOT NULL auto_increment",
		'apikey'				=> "varchar(32) NOT NULL",
		'day_limit'				=> "int(6) NOT NULL",
		'day_requests'			=> "int(6) default 0",
		'day_start_time'		=> "int(12) default 0",
		'requests'		 		=> "int(10) default 0",
		'email'					=> "varchar(64) NOT NULL",
		'ip_address'			=> "varchar(16) NOT NULL",
		'time_registered'		=> "int(12) NOT NULL",
		'secret_key'			=> "varchar(32) NOT NULL",
		'user_id' 				=> "bigint(20) default 0",
	);
	
	public $primary_key = 'aid';
	
	public $unique_keys = array(
		'apikey'		=> 'apikey',
	);
	
	public $keys = array(
		'email'			=> 'email',
		'ip_address'	=> 'ip_address',
		'user_id'		=> 'user_id',
	);
	
	public $_object_class = 'Api_Auth_Object';
	
	
	function get_apikey_by( $field, $value ){
		$result = null;
		
		if ( !$this->is_column($field) )
			return 'Invalid field ' . $field;
		
		if ( $field !== $this->primary_key && !in_array($field, $this->unique_keys) && !in_array($field, $this->keys) ){
			return 'Cannot query database using non-indexed field ' . $field;
		}
		
		$result = $this->query_by( $field, $value );
		
		if ( $result )
			$result = $result->apikey;
		
		return $result;
	}
	
	
	function get_auth_object( $apikey ){
		
		$result = $this->query_by('apikey', $apikey);
		
		return $result;
	}
	
	function generate_apikey( $email, $secret_key ){
		
		if ( !is_email($email) )
			return 'Invalid email address.';
		
		if ( strlen($secret_key) > 32 )
			return 'Secret key must be 32 characters or less.';
		
		if ( is_user_logged_in() ){
			$user_id = get_current_user_ID();	
		}
		else {
			$user_id = 0;	
		}
		
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$apikey = substr( hash_hmac('sha1', $email, API_DIGEST_KEY), 4, 32 );
		
		switch ($user_id) {
			case 0:
				$limit = 250;
				break;
			case 1:
				$limit = 10000;
				break;
			default:
				$limit = 500;
				break;
		}
		
		$this->insert( array( 
			'apikey'			=> $apikey,
			'day_limit'			=> (int) $limit,
			'email'				=> $email,
			'ip_address'		=> $ip,
			'time_registered'	=> time(),
			'secret_key' 		=> wp_filter_post_kses($secret_key),
			'user_id'			=> (int) $user_id,
		) );
		
		$sitename = get_bloginfo('name');
		$siteurl = get_bloginfo('url');
		wp_mail( $email, 'Your API key for ' . $sitename, "Hi,\n\n Your new API key for $sitename at $siteurl is <b>$apikey</b>.\n\n Be sure to save this email for future reference. \n" );
		
		return $apikey;
	}
	
	function do_api_request( $apikey ){
		
		$result = $this->get_results( "SELECT day_limit, day_requests, day_start_time, requests FROM {$this->table} WHERE apikey LIKE '$apikey' LIMIT 1" );
				
		if ( empty($result) )
			return 'Invalid API key';
		
		$result = array_shift($result);
				
		$update = array();
				
		if ( ($result->day_start_time + DAY_IN_SECONDS) >= time() ){
			
			if ( $result->day_requests >= $result->day_limit )
				return 'Daily request limit reached';	
			else
				$update['day_requests'] = $result->day_requests + 1;
		}
		else {
			$update['day_start_time'] = time();
			$update['day_requests'] = 1;
		}
		
		$update['requests'] = $result->requests + 1;
		
		$requests_remaining = $result->day_limit - $update['day_requests'];
		
		$timestamp_start = isset($update['day_start_time']) ? $update['day_start_time'] : $result->day_start_time;
		
		$requests_reset = ($timestamp_start + DAY_IN_SECONDS - time())/(60*60);
		
		$reset_hours = floor($requests_reset);
		
		$reset_mins = floor(($requests_reset - $reset_hours)*60);
		
		$this->update( $update, array('apikey' => $apikey) );
		
		return array( 'requests_remaining' => $requests_remaining, 'requests_reset' => $reset_hours . ' hours, ' . $reset_mins . ' minutes' );
	}
	
	function retrieve_forgotton_apikey( $email, $secret_key ){
		
		$result = $this->query_by('email', $email);
		
		if ( $secret_key === $result->secret_key ){
			return $result->apikey;
		}
		
		return 'Invalid secret key.';
	}
	
}