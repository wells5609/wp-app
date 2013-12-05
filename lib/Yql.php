<?php
if ( class_exists("YQL_Request") ){
	
class Yql {
	
	public $store;
	
	public $queries = array(
		'companyInfo' => array(
			'url' => 'sec.xml',
			'query' => 'SELECT * FROM xml where url="__URL__"',
			'data' => 'companyInfo',
		),
		'company_name' => array(
			'url' => 'msn',
			'query' => 'select CompanyName from json where url="__URL__"',
		),
		'description' => array(
			'url' => 'yahoo.profile',
			'query' => 'select * from html where url="__URL__" and xpath=\'//td[@class="yfnc_modtitlew1"]/p[2]\'',
		),
		'cdp' => array(
			'url' => 'google',
			'query' => 'select * from html where url="__URL__" and xpath=\'//a[@id="m-cdi"]\'',
		),
		'exchange' => array(
			'table' => 'quotes',
			'query' => 'use "__TABLE__" as quotes; select StockExchange from quotes where symbol="__VAR__"',
		),
		'sector_industry_employees' => array(
			'table' => 'stocks',
			'query' => 'use "__TABLE__" as stocks; select Sector, Industry, FullTimeEmployees from stocks where symbol="__VAR__"',
		),
		'marketcap' => array(
			'table' => 'keystats',
			'query' => 'use "__TABLE__" as keystats; select MarketCap from keystats where symbol="__VAR__"',
		),
		'sec_filings' => array(
			'url' => 'sec.xml',
			'query' => 'SELECT * FROM xml WHERE url="__URL__"',
			'data' => 'results',
		),
		'sec_filings_feed' => array(
			'url' => 'sec.feed',
			'query' => 'SELECT entry.content FROM xml WHERE url="__URL__"',
		),
		'sec_annual_reports'	=> 'sec_filings',
		'sec_filing_proxies'	=> 'sec_filings_feed',
		'sec_filing_10q'		=> 'sec_filings_feed',
		'sec_filing_8k'			=> 'sec_filings_feed',
		'sec_filing_6k'			=> 'sec_filings_feed',
	);
	
	
	static protected $_instance;
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	private function __construct(){
		$this->store =& YqlStore::instance();
	}
	
	// Adds filters for YQL query based on existance of function
	// Means don't have to call add_filter() when not needed
	protected function add_filters( $name ){
		
		// URL filter
		if ( function_exists("_yql_url_{$name}") ){
			add_filter("yql/query/url/{$name}", "_yql_url_{$name}", 10, 3);
		}
		
		// Results filter
		if ( function_exists("_yql_results_{$name}") ){
			add_filter("yql/query/results/{$name}", "_yql_results_{$name}", 10, 3);
		}
			
	}
	
	public function query( $name, $arg, $var = null ){
		
		$_this = self::instance();
				
		if ( !isset($_this->queries[$name]) ){
			throw new Exception('Invalid YQL query name: ' . $name );
		}
		
		$q = $_this->queries[$name];
		
		// string? => referencing another query
		if ( is_string($q) && isset($_this->queries[$q]) ){
			$q = $_this->queries[$q];
		}
		
		$query = $q['query'];
		
		$_this->add_filters($name);
		
		if (isset( $q['url'] ) ){
			$url = apply_filters("yql/query/url/{$name}", $_this->store->get_url( $q['url'] ), $arg, $var);
			$query = str_replace('__URL__', $url, $query);
		}
		
		if ( isset( $q['table'] ) ){
			$table = $_this->store->get_table_url( $q['table'] );
			$query = str_replace('__TABLE__', $table, $query);
		}
		
		$query = str_replace('__VAR__', $arg, $query);
		$query = apply_filters("yql/query/before/{$name}", $query, $arg, $var);
		
	//	vardump( 'Yql::query( "' . $name . '"); ' . $query);
		
		$results = yql_request( $query );
		
		if ( yql_has_results($results) ){
			
			if ( !isset( $q['data'] ) ){
				$r = $results;
			}
			else { 
				$r = array();
				if ( !is_array($results) )
					$results = (array) $results;
				if ( is_array($q['data']) ){
					foreach($q['data'] as $data){
						$r[ $data ] = !isset($results[ $data ]) ? null : $results[ $data ];	
					}
				}
				elseif ( isset($results[ $q['data'] ]) )
					$r = $results[ $q['data'] ];
			}
		}
		else $r = null;
		
		return apply_filters( "yql/query/results/{$name}", $r, $arg, $var );
	}
	
}

} // class_exists("YQL_Request")

/** YQL query result filters */

// CDP	
	function _yql_results_cdp( $results, $ticker, $var = null ){
		if ( empty($results) )
			return;
		$results->href		= urldecode( str_replace('//www.google.com/url?source=finance&q=', '', $results->href) );
		$results->content	= str_replace('/100', '', $results->content);
		$return				= new stdClass();
		$return->href		= trim($results->href);
		$return->score		= trim($results->content);
		$return->company_id	= trim( str_between($results->href, 'company=', '&ei') );
		return $return;
	}

// Company name
	function _yql_results_company_name( $results, $ticker, $var = null ){
		$title = $results->CompanyName;
		$words = array('Cl A','Cl B','Cl C','Class A','Class B','ADS','ADR');
		foreach($words as $word){
			if ( strpos($title, $word) !== false )
				$title = str_replace($word, '', $title);
		}
		return str_strip_unicode($title);
	}

// Exchange	
	function _yql_results_exchange( $results, $ticker, $var = null ){
		return trim($results->StockExchange);
	}

// marketcap
	function _yql_results_marketcap( $results, $ticker, $var = null ){
		if ( !is_object($results) ) return;
		$num = $results->MarketCap;
		$billion = $num->content/1000000000;
		return number_format($billion, 2);
	}

// sec_filings
	function _yql_results_sec_filings( $results, $ticker, $var = null ){
		return $results->filing;
	}
	
// sec_filings_feed	
	function _yql_url_sec_filings_feed( $url, $ticker, $var = null ){
		return $url . '&count=50';
	}
	function _yql_results_sec_filings_feed( $results, $ticker, $var = null ){
		$filings = array();
		foreach($results as $object){
			
			$content = (array) $object->entry->content;
			$keys = array_keys($content);
			$vals = array_values($content);
			
			foreach($keys as &$key)
				$key = str_replace(array('filing-','-'), '', $key);
			
			$filing = (object) array_combine($keys, $vals);
			
			if ( !isset($filings[ $filing->type ]) )
				$filings[ $filing->type ] = array();
			
			$filings[ $filing->type ][ $filing->date ] = $filing;
		}
		return $filings;
	}
	

// sec_annual_reports
	function _yql_url_sec_annual_reports( $url, $ticker, $var = null ){
		$form = get_annual_report_form( $ticker );
		return $url . '&type=' . $form . '&count=10';
	}

	function _yql_results_sec_annual_reports( $results, $ticker, $var = null ){
		return $results->filing;
	}

// sec_filing_proxies
	function _yql_url_sec_filing_proxies( $url, $ticker, $var = null ){
		return $url . '&type=def+14a&count=10';
	}
	function _yql_results_sec_filing_proxies( $results, $ticker, $var = null ){
		return _yql_results_sec_filings_feed( $results, $ticker, $var );
	}

// sec_filing_8k
	function _yql_url_sec_filing_8k( $url, $ticker, $var = null ){
		return $url . '&type=8-k&count=6';
	}
	function _yql_results_sec_filing_8k( $results, $ticker, $var = null ){
		return _yql_results_sec_filings_feed( $results, $ticker, $var );
	}
	
// sec_filing_10q
	function _yql_url_sec_filing_10q( $url, $ticker, $var = null ){
		return $url . '&type=10-q&count=4';
	}
	function _yql_results_sec_filing_10q( $results, $ticker, $var = null ){
		return _yql_results_sec_filings_feed( $results, $ticker, $var );
	}
	
// sec_filing_6k
	function _yql_url_sec_filing_6k( $url, $ticker, $var = null ){
		return $url . '&type=6-k&count=10';
	}
	function _yql_results_sec_filing_6k( $results, $ticker, $var = null ){
		return _yql_results_sec_filings_feed( $results, $ticker, $var );
	}
	
function get_annual_report_form( $ticker ){
	$country = get_company_country( get_company_id_from_ticker($ticker) );
	switch($country){
		case 'US': // US
			$form = '10-k';
			break;	
		case 'CA': // Canada
			$form = '40-f';
			break;
		case 'GB':
		default: // Other foreign issuers
			$form = '20-f';
			break;
	}
	return $form;
}


?>