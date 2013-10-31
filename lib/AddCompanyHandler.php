<?php

class AddCompanyHandler extends FormHandler {
	
	protected $validations = array(
		'validate_honey',
		'validate_human',
		'validate_nonce',
		'validate_ticker',
		'check_if_company_exists',
	);
	
	protected $pre_processes = array(
		'do_name', 
		'do_sec',
		'do_description', 
		'do_cdp',
		'do_exchange', 
		'do_sector_industry_employees',
	);
	
	protected $processes = array(
		'insert_wp_post', 
		'set_post_terms',
		'insert_postx', 
		'set_company_meta',
	);
	
	protected $HUMAN_CHECK = 'bad';
	protected $NONCE_NAME = 'add-company-nonce';
	
	// Extra vars
	protected $ticker;
	
	protected $model;
	
	protected $postx = array();
	
	protected $company_meta = array();
	
	
	/** Setup */
	function init(){
		@set_time_limit(120);
		$this->ticker = wp_filter_kses(strtoupper($this->POST['ticker']));
		$this->model = Registry::get('company');
	}
	
	/** Validations */
	function validate_ticker(){
		if ( empty($this->POST['ticker']) ){
			$this->errors['ticker'] = 'You must enter a ticker.';
		}	
	}
	function check_if_company_exists(){
		if ( $this->model->query_by('ticker', $this->ticker) ){
			$this->errors['exists'] = 'A company with that ticker exists.';
		}
	}
	
	/** Processing */
	
	// Overwritten method - called from insert_wp_post()
	function set_wp_post_for_insert(){
		
		$this->wp_post['post_type']		= 'company';
		$this->wp_post['post_status']	= 'publish';
		$this->wp_post['post_author']	= get_current_user_ID();
		$this->wp_post['post_name']		= $this->ticker;
			
	}
	
	// custom methods
	
	function insert_postx(){
		$insert = $field_formats = array();
		
		$insert['id'] = $this->post_id;
		$field_formats[] = '%d';
		$insert['ticker'] = $this->ticker;
		$field_formats[] = '%s';
		
		foreach($this->postx as $field => $value){
			$insert[$field] = $value;
			$field_formats[] = $this->model->get_field_format($field);
		}
		
		$this->model->insert( $insert, $field_formats );
	}
	
	function set_company_meta(){
		
		$meta_model = get_meta_model('company');
			
		foreach($this->company_meta as $meta_key => $meta_value){
		
			$meta_model->insert( 
				array(
					'post_id' => $this->post_id,
					'ticker' => $this->ticker,
					'meta_key' => $meta_key,
					'meta_value' => $meta_value,
				),
				array('%d', '%s', '%s', '%s')
			);
		}
	}
	
	
	/** Pre-proccessing */
	
	function do_sector_industry_employees(){
		
		$data = YqlDataFetcher::query('sector_industry_employees', $this->ticker);
		if ( !$data ) // try again
			$data = YqlDataFetcher::query('sector_industry_employees', $this->ticker);
		if ( $data ) {
			$_sector	= trim($data['Sector']);
			$_industry	= trim($data['Industry']);
			$_fte		= trim($data['FullTimeEmployees']);
			
			$sector = $this->create_term_if_not_exists($_sector, 'industry', array('parent' => 0));
			$industry = $this->create_term_if_not_exists($_industry, 'industry', array('parent' => $sector['term_id']));
			
			$this->postx['full_time_employees']		= (int) $_fte;
			
			$this->postx['sector']					= $_sector;
			$this->postx['industry']				= $_industry;
			
			$this->post_terms['industry']			= array($sector['term_id'], $industry['term_id']);
		}
	}
	
	function do_sec(){
		
		$sec = YqlDataFetcher::query('sec', $this->ticker);
		if ( !$sec ) // try again
			$sec = YqlDataFetcher::query('sec', $this->ticker);
		if ( $sec ) {
			$_sic		= trim( $sec['SIC'] );
			$_sic_desc	= ucwords( strtolower( $sec['SICDescription'] ) );
			
			$sic = $this->create_term_if_not_exists($_sic, 'sic', array('description' => $_sic_desc));
			
			$this->postx['sic']						= $_sic;
			$this->post_terms['sic']				= strval($_sic);
			
			$this->postx['cik'] 					= $sec['CIK'];	
			$this->postx['country']					= CountriesStates::iso_from_sec_code($sec['Location']);
			$this->postx['state']					= $sec['Location'];
			$this->postx['state_inc']				= isset($sec['stateOfIncorporation']) ? $sec['stateOfIncorporation'] : $sec['Location'];
			
			// Addresses
			
			// 1. business
			$busn = $sec['businessAddress'];
			
			if ( $busn ){
				$business_address = array(
					'street'	=> $busn['street'],
					'city'		=> $busn['city'],
					'state'		=> $busn['state'],
					'zip'		=> $busn['zipCode'],
				);
				if ( isset($busn['street2']) && $busn['street2'] != $busn['street'] )
					$business_address['street2'] = $busn['street2'];
				
				$this->company_meta['phone']		= $busn['phoneNumber'];
			}
			
			// 2. mailing
			$mail = $sec['mailingAddress'];
			
			if ( $mail ){
				$mailing_address = array(
					'street'	=> $mail['street'],
					'city'		=> $mail['city'],
					'state'		=> $mail['state'],
					'zip'		=> $mail['zipCode'],
				);
				if ( isset($mail['street2']) && $mail['street2'] != $mail['street'] )
					$mailing_address['street2'] = $mail['street2'];
			}
			
			$addresses = array();
			if ( isset($business_address) )
				$addresses['business'] = $business_address;
			if ( isset($mailing_address) )
				$addresses['mailing'] = $mailing_address;
			
			$this->company_meta['addresses']		= json_encode($addresses, JSON_FORCE_OBJECT);	
		}	
	}
	
	function do_exchange(){
		$exchange = YqlDataFetcher::query('exchange', $this->ticker);
		if ( !$exchange ) // try again
			$exchange = YqlDataFetcher::query('exchange', $this->ticker);
		if ( $exchange ){
			if ( 'NYSE' == $exchange ) $desc = 'New York Stock Exchange';
			else $desc = '';
			
			$exchg = $this->create_term_if_not_exists($exchange, 'exchange', array('description' => $desc));
			
			$this->postx['exchange']				= $exchange;
			$this->post_terms['exchange']			= strval($exchange);
		}
	}
	
	function do_cdp(){
		$cdp = YqlDataFetcher::query('cdp', $this->ticker);
		if ( !$cdp ) // try again
			$cdp = YqlDataFetcher::query('cdp', $this->ticker);
		if ( $cdp ){
			
			$this->postx['cdp_score']				= $cdp['score'];
			$this->company_meta['cdp_company_id']	= $cdp['company_id'];
		}
	}
	
	function do_name(){
		$name = YqlDataFetcher::query('company_name', $this->ticker);
		if ( !$name ) // try again
			$name = YqlDataFetcher::query('company_name', $this->ticker);
		if ( $name ){
			
			$this->wp_post['post_title']			= wp_filter_post_kses($name);
		}
	}
	
	function do_description(){
		$desc = YqlDataFetcher::query('description', $this->ticker);
		if ( !$desc ) // try again
			$desc = YqlDataFetcher::query('description', $this->ticker);
		if ( $desc ){
			
			$this->postx['description']				= trim(wp_filter_nohtml_kses($desc));		
		}
	}
	
}

?>