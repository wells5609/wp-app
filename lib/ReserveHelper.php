<?php

class ReserveHelper {
	
	public $resources = array(
		'oil' => array(
			'crude' => 'Crude Oil',
			'bitumen' => 'Bitumen',
			'synthetic' => 'Synthetic Oil (SCO)',
		),
		'gas' => 'Natural Gas',
		'coal' => array(
			'bituminous' => 'Bituminous Coal',
			'subbituminous' => 'Sub-bituminous Coal',
			'lignite' => 'Lignite Coal',
			'anthracite' => 'Anthracite Coal',
		),
		'ngl' => 'Natural Gas Liquids',
	);
	
	public $classifications = array(
		'proven' => array(
			'proved', 'p1'
		),
		'probable' => 'p2'
	);
	
	public $units = array(
		'oil'			=> 'mmbbl',
		'gas'			=> 'bcf',
		'coal'			=> 'mt',
		'ngl'			=> 'mmbbl',
	);
	
	public $co2_per_unit = array(
		'oil.crude'			=> 0.0031,
		'oil.bitumen'		=> 0.00341, // 110% of crude
		'oil.synthetic'		=> 0.00341, //    "  "
		'gas'				=> 0.000042,
		'coal'				=> 0.0016,
		'coal.bituminous'	=> 0.0019,
		'coal.subbituminous'=> '',
		'coal.lignite'		=> '',
		'coal.anthracite'	=> '',
		'ngl'				=> 0.00155, // 50% of crude
	);
	
	public $mmboe_per_unit = array(
		'oil.crude'			=> 1,
		'oil.bitumen'		=> 1,
		'oil.synthetic'		=> 1,
		'gas'				=> 0.1667,
		'coal.bituminous'	=> '',
		'coal.subbituminous'=> '',
		'coal.lignite'		=> '',
		'coal.anthracite'	=> '',
		'ngl'				=> '',
	);
	
	static protected $_instance;
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
		
		
	function convert_to_co2( $type, $quantity ){
		
		$_this = self::instance();
		
		if ( isset($_this->co2_per_unit[$type]) ){
			return $_this->co2_per_unit[$type] * $quantity;
		}
		
		$ct = $_this->parseCategoryType($type);
		
		if ( !empty($ct['type']) && isset($_this->co2_per_unit[ $ct['type'] ]) ){
			return $_this->co2_per_unit[ $ct['type'] ] * $quantity;
		}
		
		if ( isset($_this->co2_per_unit[ $ct['category'] ]) ){
			return $_this->co2_per_unit[ $ct['category'] ] * $quantity;
		}
	}
	
	function convert_to_mmboe( $type, $quantity ){
		
		$_this = self::instance();
		
		if ( isset($_this->mmboe_per_unit[$type]) ){
			return $_this->mmboe_per_unit[$type] * $quantity;
		}
		
		$ct = $_this->parseCategoryType($type);
		
		if ( !empty($ct['type']) && isset($_this->mmboe_per_unit[ $ct['type'] ]) ){
			return $_this->mmboe_per_unit[ $ct['type'] ] * $quantity;
		}
		
		if ( isset($_this->mmboe_per_unit[ $ct['category'] ]) ){
			return $_this->mmboe_per_unit[ $ct['category'] ] * $quantity;
		}
		
	}
	
	function get_resource_label($category, $type = null){
		
		$_this = self::instance();
		
		if ( null === $type )
			$ct = $_this->parseCategoryType($category);
		
		else
			$ct = array(
				'category' => $category,
				'type' => $type,
			);
		
		if ( !empty($ct['type']) && isset($_this->resources[ $ct['category'] ][ $ct['type'] ]) )
			return $_this->resources[ $ct['category'] ][ $ct['type'] ];
		
		elseif ( isset($_this->resources[ $ct['category'] ]) )
			return $_this->resources[$category];
		
	}
	
	
	function get_classification($name){
		
		$_this = self::instance();
		
		$name = strtolower($name);
		
		if ( isset($_this->classifications[$name]) )
			return $_this->classifications[$name];
		
		foreach($_this->classifications as $c => $a){
		
			if ( $a == $name || (is_array($a) && in_array($name, $a)) )
				return $c;
		}
	}
	
	function parseCategoryType( $arg ){
		if ( strpos($arg, '.') !== false ){
			$parts = explode('.', $arg);
			$category = $parts[0];
			$type = $parts[1];
		}
		else {
			$category = $arg;
			$type = null;
		}
		return array(
			'category' => $category, 
			'type' => $type
		);
	}

}
