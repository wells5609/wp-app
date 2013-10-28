<?php

class YqlStore {
	
	static public $base = 'http://query.yahooapis.com/v1/public/yql?q=';
	
	static public $tables = array(
		'keystats'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.keystats.xml',
		'stocks'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.stocks.xml',
		'sectors'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.sectors.xml',
		'industry'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.industry.xml',
		'quotes'	=> 'http://www.datatables.org/yahoo/finance/yahoo.finance.quotes.xml',
	);
	
	static public $envs = array(
		'alltables' => 'store://datatables.org/alltableswithkeys',
	);
	
	static public $urls = array(
		'msn'		=> 'http://quotes.money.msn.com/json?symbol=',
		'yahoo'		=> 'http://finance.yahoo.com/q/',
		'google'	=> 'http://www.google.com/finance?q=',
		'sec'		=> 'http://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&output=xml&CIK=',
	);
	
	static public $sub_urls = array(
		'yahoo' => array(
			'profile' => 'pr?s=__VAR__+Profile',
		),
	);
	
	static public $xpaths = array(
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
	
	
	static public function add_table($name, $url){
		self::$tables[$name] = $url;	
	}
	
	static public function add_env($name, $url){
		self::$envs[$name] = $url;	
	}
	
	static public function get_url($url_name){
		
		$url = false;
		
		if ( strpos($url_name, '.') !== false ){
			$sub_url = array_get_dot( self::$sub_urls, $url_name );
			$url_name = substr( $url_name, 0, strpos($url_name, '.'));
		}
		
		if ( isset(self::$urls[$url_name]) ){
			$url = self::$urls[$url_name];	
		}
		
		if ( isset($sub_url) && $sub_url ){
			$url .= $sub_url;
		}
		
		return $url;
	}
	
	static public function get_table_url($name){
		if ( isset(self::$tables[$name]) ){
			return self::$tables[$name];	
		}
		return false;
	}
	
	static public function get_sub_url($url_name, $sub_url_name){
		if ( isset(self::$sub_urls[$url_name]) ){
			if ( isset(self::$sub_urls[$url_name][$sub_url_name]) )
				return self::$sub_urls[$url_name][$sub_url_name];
		}	
		return false;
	}
	
	static public function get_xpath($xpath){
		
		if ( strpos($xpath, '.') !== false ){
			return array_get_dot( self::$xpaths, $xpath);
		}
		
		if ( isset(self::$xpaths[$xpath]) ){
			if ( isset(self::$xpaths[$url_name][$xpath]) )
				return self::$xpaths[$url_name][$xpath];
		}
		
		return false;
	}
	
}



?>