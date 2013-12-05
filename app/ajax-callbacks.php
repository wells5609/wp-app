<?php
class AppAjax extends Api_Ajax_Callbacks {
	
	protected $data_unavailable = '<small class="text-muted">Data unavailable</small>';
	
	public $actions = array(
		'add_company' => 1,
		'add_company_reserves' => 1,
		'get_company_marketcap' => 0,
		'update_company_marketcap' => 1,
		'get_company_sec_annual_reports' => 0,
		'get_company_sec_filings_detail' => 0,
		'get_company' => 0,
	);
	
	static protected $_instance;
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}

	function sec_filing_lis( array $filings, $show_info = false, $is_feed = false ){
		
		$s = '';
		
		foreach($filings as $f){
			
			if ( $is_feed ){
				$href = $f->href;
				$title = $f->type . ' - ' . $f->size;	
				$content = $f->date;
				$small = $f->type;
			}
			else {
				$href = $f->filingHREF;
				$title = 'Filed on ' . $f->dateFiled;
				$content = substr($f->dateFiled, 0, 4);
				$small = $f->type;
			}
			
			$s .= '<li><a href="' . esc_url( $href ) . '" rel="external" title="' . esc_attr( $title ) . '">'
				. $content . ' <small>(' . $small . ')</small></a></li>';
		}
		
		return $s;
	}
	
	public function get_company_sec_annual_reports( ){
		
		global $api;
		
		if ( !ajax_verify_nonce() )
			die( alert('error', "Invalid request") );
		
		$ticker = format_ticker( $api->query->q, 'sec' );
		
		$filings = Yql::query('sec_annual_reports', $ticker);
		if ( !$filings ) $filings = Yql::query('sec_annual_reports', $ticker);
		if ( !$filings ) die( $this->data_unavailable );
		
		if ( $api->is_json() ) {
			
			ajax_send_json_success( $filings, isset($api->query->jsonp_callback)? $api->query->jsonp_callback : null );
		}
		else {
			
			$api->response->set_content_type("html");
		
			$s = '<h5>Annual Reports</h5>';
			$s .= '<ul class="list-unstyled chevrons" id="' . $ticker . '-annual-reports">';
			$s .= self::sec_filing_lis( (array) $filings );
			$s .= '</ul>';
			
			die( $s );	
		}
		
		exit;
	}
	
	
	public function get_company_sec_filings_detail(){
		
		global $api;
		
		if ( !ajax_verify_nonce() )
			die( alert('error', "Invalid request") );	
		
		$api->response->set_content_type("html");
		
		$ticker = format_ticker($api->query->q, 'sec');
		
		if ( $cached = cache_get('company_sec_filings_detail/'.$ticker) )
			die( $cached );
		
		$country = get_company_country( get_company_id_from_ticker($ticker) );
		
		$annual = Yql::query('sec_annual_reports', $ticker);
		
		if ( 'US' == $country ){
			$proxy = Yql::query('sec_filing_proxies', $ticker);
			$proxy =  array_shift( $proxy );
			$quarter = Yql::query('sec_filing_10q', $ticker);
			$current = Yql::query('sec_filing_8k', $ticker);
			$quarter = array_shift($quarter);
			$other = array_shift($current);
		}
		else {
			$other = Yql::query('sec_filing_6k', $ticker);
			$other = array_shift($other);
		}
		
		$coldiv = '<div class="col-sm-' . (isset($proxy) ? '3' : '6') . '">';
		$colend = '</div>';
		
		$s = '<div id="sec-filings" class="row">';
		
		$s .= $coldiv . '<h5>Annual Reports</h5>';
		if ( $annual ){
			$s .= '<ul class="list-unstyled chevrons" id="' . $ticker . '-annual-reports">';
			$s .= self::sec_filing_lis( $annual );
			$s .= '</ul>';
		}
		else {
			$s .= text_label($this->data_unavailable);	
		}
		$s .= $colend;
		
		if ( isset($proxy) && $proxy ){
			$s .= $coldiv . '<h5>Proxies</h5>';
			$s .= '<ul class="list-unstyled chevrons" id="' . $ticker . '-proxies">';
			$s .= self::sec_filing_lis( $proxy, false, true );
			$s .= '</ul>' . $colend;
		}
		if ( isset($quarter) && $quarter ){
			$s .= $coldiv . '<h5>Quarterly Reports</h5>';
			$s .= '<ul class="list-unstyled chevrons" id="' . $ticker . '-quarterly-reports">';
			$s .= self::sec_filing_lis( $quarter, false, true );
			$s .= '</ul>' . $colend;
		}
		
		$s .= $coldiv . '<h5>Other Reports</h5>';
		if ( $other ){
			$s .= '<ul class="list-unstyled chevrons" id="' . $ticker . '-other-reports">';
			$s .= self::sec_filing_lis( $other, false, true );
			$s .= '</ul>';
		}
		else {
			$s .= text_label($this->data_unavailable);	
		}
		$s .= $colend . '</div>';
		
		cache_set('company_sec_filings_detail/'.$ticker, $s);
		
		die( $s );
	}
	
	public function add_company(){
		global $api;
		
		$api->response->set_content_type("html");
		
		$args = wp_parse_args($_POST['q'], $_POST);
		
		if ( !verify_secure_nonce($args['nonce'], 'add-company-nonce') )
			die( alert('error', "Invalid request") );
		
		cache_flush_group( 'company' );
		
		$handler = new AddCompanyHandler( $args, 'POST' );
		
		die( $handler->display_messages() );
	}
	
	public function add_company_reserves() {
		global $api;
		
		$api->response->set_content_type("html");
		
		$args = wp_parse_args($_REQUEST['q'], $_REQUEST);
		
		cache_flush_group( 'reserves' );
		
		$handler = new AddReservesHandler( $args, 'POST' );
		
		die( $handler->display_messages() );
	}
	
	public function get_company_marketcap(){
		
		global $api;
		
		if ( !ajax_verify_nonce() )
			die( alert('error', 'Invalid Request') );
		
		$id = get_company_id_from_ticker( format_ticker($api->query->args['q']) );
		
		if ( empty($id) )
			die( 'n/a' );
		
		$model =& get_meta_model('company');
		$marketcap = $model->get_object( $id, 'marketcap' );
		
		if ( empty($marketcap) || $marketcap->is_expired() ){
			
			self::update_company_marketcap(false);
		}
		else 
			die( $marketcap->meta_value );
		
	}
	
	public function update_company_marketcap( $check_nonce = true ){
		
		global $api;
		
		if ( !ajax_verify_nonce() && $check_nonce )
			die( alert('error', 'Invalid Request') );
		
		$ticker = format_ticker( $api->query->q, 'yahoo');
		
		$marketcap = Yql::query('marketcap', $ticker);
		if ( !$marketcap ) $marketcap = Yql::query('marketcap', $ticker);
		if ( !$marketcap ) die( $this->data_unavailable );
			
		$id = get_company_id_from_ticker( format_ticker($ticker) );
		
		$meta =& get_meta_model('company');
		$meta->update_meta( array('post_id' => $id, 'ticker' => format_ticker($ticker), 'meta_key' => 'marketcap', 'meta_value' => $marketcap) );
		
		$model =& get_model('company');
		$success = $model->update_var( 'marketcap', $marketcap, array('id' => $id) );
			
		die( $marketcap );
	}
	
	function get_company(){
		
		global $api;
		
		if ( empty($api->query->q) )
			die('fool');
		
		$model =& get_model('company');
		
		if ( is_numeric($api->query->q) ){
			$company = get_postx($api->query->q);	
		}
		else {
			$company = $model->query_by('ticker', wp_filter_nohtml_kses($api->query->q));	
		}
		
		if ( $api->is_json() ) {
			
			ajax_send_json( $company, $api->query );
		}
		else {
			die('Company!');	
		}
	}
	
}

AppAjax::instance();