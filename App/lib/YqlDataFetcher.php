<?php

class YqlDataFetcher {
	
	static public $queries = array(
		'sec' => array(
			'url' => 'sec',
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
	);
	
	public function query( $name, $arg ){
		
		if ( isset(self::$queries[$name]) ){
			
			$q = self::$queries[$name];
			
			if (isset( $q['url'] ) )
				$url = YqlStore::get_url( $q['url'] ) . $arg;
			
			if ( isset( $q['table'] ) )
				$table = YqlStore::get_table_url( $q['table'] );
			
			$query = $q['query'];
			
			if ( isset($url) )
				$query = str_replace('__URL__', $url, $query);
			
			if ( isset($table) )
				$query = str_replace('__TABLE__', $table, $query);	
			
			$query = str_replace('__VAR__', $arg, $query);
			
			$results = yql_request( $query );
			
			if ( yql_has_results($results) ){
				
				if ( isset( $q['data'] ) && isset($results[ $q['data'] ]) )
					$results = $results[ $q['data'] ];
				
				return apply_filters( "yql/query/{$name}", $results, $arg );
			}
			
			return NULL;
		}	
		
	}
	
}

/** YQL query result filters */

// CDP
add_filter('yql/query/cdp', '_yql_parse_cdp', 10, 2);
	
	function _yql_parse_cdp( $results, $ticker ){
		
		$results['href'] = urldecode( str_replace('//www.google.com/url?source=finance&q=', '', $results['href']) );
		$results['content'] = str_replace('/100', '', $results['content']);
		
		$return = array();
		
		$return['href'] = $results['href'];
		$return['score'] = $results['content'];
		$return['company_id'] = str_between($results['href'], 'company=', '&ei');
		
		return $return;
	}

// Company name
add_filter('yql/query/company_name', '_yql_parse_company_name', 10, 2);

function _yql_parse_company_name( $results, $ticker ){
	
	return str_strip_unicode($results['CompanyName']);
}

// Exchange
add_filter('yql/query/exchange', '_yql_parse_exchange', 10, 2);

function _yql_parse_exchange( $results, $ticker ){
	
	return trim($results['StockExchange']);
}


?>