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
		'other' => array(
			'ngl' => 'Natural Gas Liquids',
			'cbm' => 'Coalbed Methane', // coalbed methane
		),
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
		'other.ngl'		=> 'mmbbl',
		'other.cbm'		=> 'bcf',
	);
	
	public $co2_per_unit = array(
		'oil.crude'			=> 0.0031,
		'oil.bitumen'		=> '',
		'oil.synthetic'		=> '',
		'gas'				=> 0.000042,
		'coal'				=> 0.0016,
		'coal.bituminous'	=> 0.0019,
		'coal.subbituminous'=> '',
		'coal.lignite'		=> '',
		'coal.anthracite'	=> '',
		'other.ngl'			=> '',
		'other.cbm'			=> '',
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
		'other.ngl'			=> '',
		'other.cbm'			=> '',
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
		
		$ct = $_this->determineCategoryType($type);
		
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
		
		$ct = $_this->determineCategoryType($type);
		
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
			$ct = $_this->determineCategoryType($category);
		
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
		
			if ( (is_array($a) && in_array($name, $a)) || $a == $name )
				return $c;
		}
	}
	
	function determineCategoryType( $arg ){
		if ( strpos($arg, '.') === false ){
			$category = $arg;
			$type = null;
		}
		else {
			$parts = explode('.', $arg);
			$category = $parts[0];
			$type = $parts[1];
		}
		return array(
			'category' => $category, 
			'type' => $type
		);
	}

}
