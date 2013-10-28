<?php

class AddCompanyHandler extends FormHandler {
	
	protected $send_error_if = array(
		'company_exists',
	);
	
	protected $validations = array(
		'validate_honey',
		'validate_human',
		'validate_nonce',
		'validate_ticker',
	);
	
	protected $pre_processes = array(
		'do_name', 'do_sec',
		'do_description', 'do_cdp',
		'do_exchange', 'do_sector_industry_employees',
	);
	
	protected $processes = array(
		'insert_post', 'set_terms',
		'insert_postx', 'set_company_meta',
	);
	
	
	protected $ticker;
	
	protected $model;
	
	protected $wp_post = array();
	
	protected $postx = array();
	
	protected $company_meta = array();
	
	protected $terms = array(); // array of taxonomy => term
	
	
	function setup(){
		$this->ticker = wp_filter_kses(strtoupper($this->POST['ticker']));
		$this->model = ModelRegistry::get('company');
	}
	
	function company_exists(){
		if ( $this->model->query_by('ticker', $this->ticker) ){
			$this->errors['exists'] = 'A company with that ticker exists.';
			return true;
		}
		return false;	
	}
	
	function validate_ticker(){
		if ( empty($this->POST['ticker']) ){
			$this->errors['ticker'] = 'You must enter a ticker.';
		}	
	}
	
		
	function insert_post(){
		
		$this->wp_post['post_type']		= 'company';
		$this->wp_post['post_status']	= 'publish';
		$this->wp_post['post_author']	= get_current_user_ID();
		$this->wp_post['post_name']		= $this->ticker;
		
		$this->post_id = wp_insert_post($this->wp_post);
		
		if ( 0 !== $this->post_id ){
			$this->msgs[] = '<h4>Success!</h4>'
				. get_the_title($this->post_id) . " ({$this->ticker}) was added to the database."
				. ' <a class="alert-link" href="' . esc_url(get_permalink($this->post_id)) . '">View &raquo;</a>';
		}
		
		return $this->post_id;
	}
	
	function insert_postx(){
		$insert = $field_formats = array();
		
		$insert['id'] = $this->post_id;
		$field_formats[] = '%d';
		$insert['ticker'] = $this->ticker;
		$field_formats[] = '%s';
		
		foreach($this->postx as $field => $value){
			$insert[$field] = $value;
			$field_formats[] = $this->model->schema->get_field_format($field);
		}
		
		$this->model->insert( $insert, $field_formats );
	}
	
	function set_terms(){
		
		foreach($this->terms as $taxonomy => $terms){
			
			if ( !is_array($terms) )
				$terms = array($terms);
			
			wp_set_post_terms($this->post_id, $terms, $taxonomy, false);
		}
	}
	
	function set_company_meta(){
		
		foreach($this->company_meta as $meta_key => $meta_value){
			
			$meta_model = get_meta_model('company');
			
			$meta_model->insert( array(
				'post_id' => $this->post_id,
				'ticker' => $this->ticker,
				'meta_key' => $meta_key,
				'meta_value' => $meta_value,
				),
				array('%d', '%s', '%s', '%s')
			);
		}
	}
	
	function do_sector_industry_employees(){
		
		$data = YqlDataFetcher::query('sector_industry_employees', $this->ticker);
		if ( !$data ) // try again
			$data = YqlDataFetcher::query('sector_industry_employees', $this->ticker);
		if ( $data ) {
			$_sector = trim($data['Sector']);
			$_industry = trim($data['Industry']);
			$_fte = trim($data['FullTimeEmployees']);
			
			$sector = $this->create_term_if_not_exists($_sector, 'industry', array('parent' => 0));
			$industry = $this->create_term_if_not_exists($_industry, 'industry', array('parent' => $sector['term_id']));
			
			$this->postx['sector'] = $_sector;
			$this->postx['industry'] = $_industry;
			
			$this->terms['industry'] = array($sector['term_id'], $industry['term_id']);
			
			$this->postx['full_time_employees'] = (int) $_fte;
			
		}
	}
	
	function do_sec(){
		
		$sec = YqlDataFetcher::query('sec', $this->ticker);
		if ( !$sec ) // try again
			$sec = YqlDataFetcher::query('sec', $this->ticker);
		
		if ( $sec ) {
	
			$this->postx['cik'] = $sec['CIK'];	
			
			$this->postx['country']		= CountriesStates::iso_from_sec_code($sec['Location']);
			$this->postx['state']		= $sec['Location'];
			$this->postx['state_inc']	= $sec['stateOfIncorporation'];
				
			// SIC
			$this->_sec_sic($sec);
			
			// Addresses
			$this->_sec_addresses($sec);
		}	
	}
	
	function _sec_sic($results){
		$_sic = $results['SIC'];
		$_sic_desc = ucwords(strtolower($results['SICDescription']));
		$sic = $this->create_term_if_not_exists($_sic, 'sic', array('description' => $_sic_desc));
		$this->postx['sic'] = $_sic;
		$this->terms['sic'] = strval($_sic);
	}
	
	function _sec_addresses($results){
		
		// (business)
		$b = $results['businessAddress'];
		
		if ( $b ){
			$ba = array(
				'street'	=> $b['street'],
				'city'		=> $b['city'],
				'state'		=> $b['state'],
				'zip'		=> $b['zipCode'],
			);
			if ( isset($b['street2']) && $b['street2'] != $b['street'] )
				$ba['street2'] = $b['street2'];
			
			$this->company_meta['phone'] = $b['phoneNumber'];
			
			$business_address = $ba;
		}
		
		// (mailing)
		$m = $results['mailingAddress'];
		
		if ( $m ){
			$ma = array(
				'street'	=> $m['street'],
				'city'		=> $m['city'],
				'state'		=> $m['state'],
				'zip'		=> $m['zipCode'],
			);
			if ( isset($m['street2']) && $m['street2'] != $m['street'] )
				$ma['street2'] = $m['street2'];
			
			$mailing_address = $ma;
		}
		
		$addresses = array();
		
		if ( isset($business_address) ){
			$addresses['business'] = $business_address;
		}
		if ( isset($mailing_address) ){
			$addresses['mailing'] = $mailing_address;	
		}
		
		$this->company_meta['addresses'] = json_encode($addresses, JSON_FORCE_OBJECT);	
	}
	
	function do_exchange(){
		$exchange = YqlDataFetcher::query('exchange', $this->ticker);
		if ( !$exchange ) // try again
			$exchange = YqlDataFetcher::query('exchange', $this->ticker);
		if ( $exchange ){
			if ( 'NYSE' == $exchange )
				$desc = 'New York Stock Exchange';
			else
				$desc = '';
			$exchg = $this->create_term_if_not_exists($exchange, 'exchange', array('description' => $desc));
			$this->postx['exchange'] = $exchange;
			$this->terms['exchange'] = strval($exchange);
		}
	}
	
	function do_cdp(){
		$cdp = YqlDataFetcher::query('cdp', $this->ticker);
		if ( !$cdp ) // try again
			$cdp = YqlDataFetcher::query('cdp', $this->ticker);
		if ( $cdp ){
			$this->postx['cdp_score'] = $cdp['score'];
			$this->company_meta['cdp_company_id'] = $cdp['company_id'];
		}
	}
	
	function do_name(){
		$name = YqlDataFetcher::query('company_name', $this->ticker);
		if ( !$name ) // try again
			$name = YqlDataFetcher::query('company_name', $this->ticker);
		if ( $name )
			$this->wp_post['post_title'] = $name;	
	}
	
	function do_description(){
		$desc = YqlDataFetcher::query('description', $this->ticker);
		if ( !$desc ) // try again
			$desc = YqlDataFetcher::query('description', $this->ticker);
		if ( $desc )
			$this->postx['description'] = trim($desc);		
	}
	
}

?>