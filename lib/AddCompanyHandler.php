<?php

class AddCompanyHandler extends FormHandler {
	
	protected $validations = array(
		'validate_honey',
		'validate_human',
		'validate_secure_nonce',
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
	//protected $NONCE_NAME = 'add-company-nonce';
	
	// Extra vars
	protected $ticker;
	
	protected $model;
	
	protected $postx = array();
	
	protected $company_meta = array();
	
	
	/** Setup */
	function init(){
		@set_time_limit(120);
		
		$this->_ticker	= wp_filter_kses(strtoupper($this->POST['ticker']));
		
		$this->model	= get_model('company');
		
		$this->ticker_yahoo		= format_ticker($this->_ticker, 'yahoo');
		$this->ticker_google	= format_ticker($this->_ticker, 'google');
		$this->ticker_msn		= format_ticker($this->_ticker, 'msn');
		$this->ticker_sec		= format_ticker($this->_ticker, 'sec');
	}
	
	/** Validations */
	function validate_ticker(){
		if ( empty($this->POST['ticker']) ){
			$this->errors['ticker'] = 'You must enter a ticker.';
		}	
	}
	function validate_secure_nonce(){
		if ( !ajax_verify_nonce($this->POST['nonce'], 'add-company-nonce') ){
			$this->errors['nonce'] = 'Invalid request.';
		}	
	}
	function check_if_company_exists(){
		if ( $this->model->query_by('ticker', $this->ticker_msn) ){
			$this->errors['exists'] = 'A company with that ticker exists.';
		}
	}
	
	/** Processing */
	
	// Overwritten method - called from insert_wp_post()
	function set_wp_post_for_insert(){
		
		$this->wp_post['post_type']		= 'company';
		$this->wp_post['post_status']	= 'publish';
		$this->wp_post['post_author']	= get_current_user_ID();
		$this->wp_post['post_name']		= $this->ticker_yahoo;
		$this->wp_post['post_excerpt']	= str_limit_sentences($this->postx['description'], 2);	
			
	}
	
	// custom methods
	
	function insert_postx(){
		$insert = $field_formats = array();
		
		$insert['id'] = $this->post_id;
		$field_formats[] = '%d';
		$insert['ticker'] = $this->ticker_msn;
		$field_formats[] = '%s';
		
		foreach($this->postx as $field => $value){
			$insert[$field] = $value;
			$field_formats[] = $this->model->get_column_format($field);
		}
		
		$this->model->insert( $insert, $field_formats );
	}
	
	function set_company_meta(){
		
		$meta_model = get_meta_model('company');
			
		foreach($this->company_meta as $meta_key => $meta_value){
		
			$meta_model->insert( 
				array(
					'post_id' => $this->post_id,
					'ticker' => $this->ticker_msn,
					'meta_key' => $meta_key,
					'meta_value' => $meta_value,
				),
				array('%d', '%s', '%s', '%s')
			);
		}
	}
	
	
	/** Pre-proccessing */
	
	function do_sector_industry_employees(){
		
		$data = Yql::query('sector_industry_employees', $this->ticker_yahoo);
		
		if ( !$data ) // try again
			$data = Yql::query('sector_industry_employees', $this->ticker_yahoo);
		
		if ( $data ) {
			$_sector	= trim($data->Sector);
			$_industry	= trim($data->Industry);
			$_fte		= trim($data->FullTimeEmployees);
			
			if ( 'N/A' != $_sector )
				$sector = $this->create_term_if_not_exists($_sector, 'industry', array('parent' => 0));
			if ( 'N/A' != $_industry )
				$industry = $this->create_term_if_not_exists($_industry, 'industry', array('parent' => $sector['term_id']));
			
			$this->postx['full_time_employees']		= (int) $_fte;
			$this->postx['sector']					= $_sector;
			$this->postx['industry']				= $_industry;
			if ( isset($sector) )
				$this->post_terms['industry']		= array($sector['term_id'], $industry['term_id']);
		}
	}
	
	function do_sec(){
		
		$sec = Yql::query('companyInfo', $this->ticker_sec);
		if ( !$sec ) // try again
			$sec = Yql::query('companyInfo', $this->ticker_sec);
		if ( $sec ) {
			$_sic		= trim( $sec->SIC );
			$_sic_desc	= ucwords( strtolower( $sec->SICDescription ) );
			
			$sic = $this->create_term_if_not_exists($_sic, 'sic', array('description' => $_sic_desc));
			
			$this->postx['sic']						= $_sic;
			$this->post_terms['sic']				= strval($_sic);
			$this->postx['cik'] 					= $sec->CIK;	
			$this->postx['country']					= CountriesStates::iso_from_sec_code($sec->Location);
			$this->postx['state']					= $sec->Location;
			$this->postx['state_inc']				= isset($sec->stateOfIncorporation) ? $sec->stateOfIncorporation : $sec->Location;
			
			// Addresses
			
			// 1. business
			$busn = (array) $sec->businessAddress;
			
			if ( $busn ){
				$business_address = array(
					'street'	=> $busn['street'],
					'city'		=> $busn['city'],
					'state'		=> $busn['state'],
					'zip'		=> $busn['zipCode'],
				);
				if ( isset($busn['street2']) && $busn['street2'] != $busn['street'] )
					$business_address['street2'] = $busn['street2'];
				
				$this->company_meta['phone']		= trim($busn['phoneNumber']);
			}
			
			// 2. mailing
			$mail = (array) $sec->mailingAddress;
			
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
		$exchange = Yql::query('exchange', $this->ticker_yahoo);
		if ( !$exchange ) // try again
			$exchange = Yql::query('exchange', $this->ticker_yahoo);
		if ( $exchange ){
			if ( 'NYSE' == $exchange ) $desc = 'New York Stock Exchange';
			else $desc = '';
			
			$exchg = $this->create_term_if_not_exists($exchange, 'exchange', array('description' => $desc));
			
			$this->postx['exchange']				= $exchange;
			$this->post_terms['exchange']			= strval($exchange);
		}
	}
	
	function do_cdp(){
		$cdp = Yql::query('cdp', $this->ticker_google);
		if ( !$cdp ) // try again
			$cdp = Yql::query('cdp', $this->ticker_google);
		if ( $cdp ){
			
			$this->postx['cdp_score']				= $cdp->score;
			$this->company_meta['cdp_company_id']	= $cdp->company_id;
		}
	}
	
	function do_name(){
		$name = Yql::query('company_name', $this->ticker_msn);
		if ( !$name ) // try again
			$name = Yql::query('company_name', $this->ticker_msn);
		if ( $name ){
			
			$this->wp_post['post_title']			= wp_filter_post_kses($name);
		}
	}
	
	function do_description(){
		$desc = Yql::query('description', $this->ticker_yahoo);
		if ( !$desc ) // try again
			$desc = Yql::query('description', $this->ticker_yahoo);
		if ( $desc ){
			
			$this->postx['description']				= $this->sanitize_description(wp_filter_nohtml_kses($desc));	
		}
		else
			$this->postx['description']	= '';
	}
	
	
	function sanitize_description($from){
		
		$words = array(
			'L.P.' => 'LP',
			'L.L.C.' => 'LLC',
			'Corp.' => 'Corp',
			'Co.' => 'Co',
			'p.l.c.' => 'plc',
			'P.L.C.' => 'plc',
			'Inc.' => 'Inc',
			'Cos.' => 'Cos',
			'Ltd.' => 'Ltd',
		);
		
		foreach($words as $search => $replace){
			
			if ( strpos($from, $search) !== false ){
				$from = str_replace($search, $replace, $from);	
			}
				
		}
		
		return trim($from);
	}
	
	
}

?>