<?php

class YqlStore {
	
	public $base = 'http://query.yahooapis.com/v1/public/yql?q=';
	
	public $tables = array(
		'keystats'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.keystats.xml',
		'stocks'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.stocks.xml',
		'sectors'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.sectors.xml',
		'industry'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.industry.xml',
		'quotes'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.quotes.xml',
	);
	
	public $envs = array(
		'alltables' => 'store://datatables.org/alltableswithkeys',
	);
	
	public $urls = array(
		'msn'		=> 'http://quotes.money.msn.com/json?symbol=__VAR__',
		'yahoo'		=> 'http://finance.yahoo.com/q/',
		'google'	=> 'http://www.google.com/finance?q=__VAR__',
		'sec'		=> 'http://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&CIK=__VAR__',
	);
	
	public $sub_urls = array(
		'yahoo' => array(
			'profile' => 'pr?s=__VAR__+Profile',
		),
		'sec' => array(
			'xml' => '&owner=exclude&output=xml',
			'feed' => '&owner=exclude&output=atom',
		),
	);
	
	public $queries = array(
		'yahoo' => array(
			'marketcap' => 'select MarketCap from table:keystats where symbol=__VAR__',
			'description' => 'select * from html where symbol=__VAR__ and xpath=\'//td[@class="yfnc_modtitlew1"]/p[2]\' and url:yahoo.profile',
		),
	);
	
	public $xpaths = array(
		'yahoo' => array(
			'profile' => array(
				'description' => 'xpath=\'//td[@class="yfnc_modtitlew1"]/p[2]\'',
				'iss' => 'xpath=\'//td[@class="yfnc_modtitlew2"]/table[2]/tr/td\'',
			),
		),
		'google' => array(		
			'title' => 'xpath="//div[@id=\'companyheader\']/div[1]/h3"',
			'cdp' => 'xpath=\'//a[@id="m-cdi"]\'',
		),
	);
	
	static protected $_instance;
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	public function get_query($name){
		
		$_this = self::instance();
		
		if ( strpos($name, '.') !== false ){
			$query = array_get_dot( $_this->queries, $name );
			$name = substr( $name, 0, strpos($name, '.'));
		}
		else if ( isset($_this->queries[$name]) ){
			$query = $_this->queries[$name];
		}
		else {
			return false;
		}
		
		if ( strpos($query, 'table:') !== false ){
	
			$part1 = substr( $query, strpos($query, 'table:') ); // substring "table:* ..."
			$part2 = substr( $part1, 0, strpos($part1, ' ') ); // just "table:*"
			$table = str_replace('table:', '', $part2);
			
			if ( !isset($_this->tables[$table]) )
				return false;
			
			$query = str_replace("table:{$table}", $table, $query);	
			
			$query = 'USE ' . $_this->tables[$table] . ' AS ' . $table . '; ' . $query;
		}
		elseif ( strpos($query, 'url:') !== false ){
			
			$part1 = substr( $query, strpos($query, 'url:') ); // substring "url:* ..."
			$url_name = str_replace('url:', '', $part1);
			
			$url = $_this->get_url($url_name);
			
			if ( !$url )
				return false;
			
			$query = str_replace("url:{$url_name}", '', $query);
			
			if ( stripos($query, 'where') !== false )
				$query = rtrim($query, ' andAND') . ' AND url="' . $url . '"';
			else 
				$query = $query . ' WHERE URL="' . $_this->get_url($url) . '"';
		}
		
		return $query;
	}
	
	public function add_table($name, $url){
		$_this = self::instance();
		$_this->tables[$name] = $url;
		return $_this;
	}
	
	public function add_env($name, $url){
		$_this = self::instance();
		$_this->envs[$name] = $url;
		return $_this;
	}
	
	public function get_url($url_name){
		
		$_this = self::instance();
		
		$url = false;
		
		if ( strpos($url_name, '.') !== false ){
			$sub_url = array_get_dot( $_this->sub_urls, $url_name );
			$url_name = substr( $url_name, 0, strpos($url_name, '.'));
		}
		
		if ( isset($_this->urls[$url_name]) )
			$url = $_this->urls[$url_name];	
		
		if ( isset($sub_url) && $sub_url )
			$url .= $sub_url;
		
		return $url;
	}
	
	public function get_table_url($name){
		$_this = self::instance();
		if ( isset($_this->tables[$name]) )
			return $_this->tables[$name];
		return false;
	}
	
	public function get_sub_url($url_name, $sub_url_name){
		$_this = self::instance();
		if ( isset($_this->sub_urls[$url_name]) ){
			if ( isset($_this->sub_urls[$url_name][$sub_url_name]) )
				return $_this->sub_urls[$url_name][$sub_url_name];
		}	
		return false;
	}
	
	public function get_xpath($xpath){
		
		$_this = self::instance();
		
		if ( strpos($xpath, '.') !== false )
			return array_get_dot( $_this->xpaths, $xpath);
		
		if ( isset($_this->xpaths[$xpath]) ){
			if ( isset($_this->xpaths[$url_name][$xpath]) )
				return $_this->xpaths[$url_name][$xpath];
		}
		
		return false;
	}
	
}



?>