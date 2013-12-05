<?php

class ReserveHelper {
	
	public $resources = array(
		'crude' => 'Crude Oil',
		'bitumen' => 'Bitumen',
		'synthetic' => 'Synthetic Oil',
		'ngl' => 'NGLs',
		'gas' => 'Natural Gas',
		'coal' => 'Coal',
		'coal.bituminous' => 'Bituminous Coal',
		'coal.subbituminous' => 'Sub-bituminous Coal',
		'coal.lignite' => 'Lignite Coal',
		'coal.anthracite' => 'Anthracite Coal',
	);
	
	public $classifications = array(
		'proven' => array('proved', 'p1'),
		'probable' => 'p2',
		'possible' => 'p3',
	);
	
	public $units = array(
		'crude'			=> 'mmbbl',
		'bitumen'		=> 'mmbbl',
		'synthetic'		=> 'mmbbl',
		'ngl'			=> 'mmbbl',
		'gas'			=> 'bcf',
		'coal'			=> 'mt',
	);
	
	public $co2_per_unit = array(
		'crude'			=> 0.0031,
		'bitumen'		=> 0.00341, // 110% of crude
		'synthetic'		=> 0.00341, //    "  "
		'ngl'			=> 0.001705, // 55% of crude
		'gas'			=> 0.000042,
		'coal'				=> 0.0020,
		'coal.bituminous'		=> 0.002566,
		'coal.subbituminous'	=> 0.001845,
		'coal.lignite'			=> 0.001509,
		'coal.anthracite'		=> 0.002863,
	);
	
	public $mmboe_per_unit = array(
		'crude'			=> 1,
		'bitumen'		=> 1,
		'synthetic'		=> 1,
		'ngl'			=> 1,
		'gas'			=> 0.1667,
		'coal' 			=> '',
		'coal.bituminous'		=> '',
		'coal.subbituminous'	=> '',
		'coal.lignite'			=> '',
		'coal.anthracite'		=> '',
	);
	
	// 1 boe = 5.86152 GJ | 1 GJ = 0.17060421187678 boe
	public $ej_per_unit = array(
		'crude' 		=> 6.13,
		'bitumen'		=> 6.13,
		'synthetic'		=> 6.13, 
		'ngl'			=> 4.08, 
		'gas'			=> 1.08,
		'coal'			=> 21.24,
	);
	
	const DECIMALS = 4;
	
	private static $_instance;
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}
		return self::$_instance;
	}
	
	function get_unit( $type ){
		
		$_this = self::instance();
		$type = $_this->parseType($type);
		
		if ( !empty($_this->units[$type]) )
			return $_this->units[$type];
		
		elseif ( str_startswith($type, 'coal') )
			return $_this->units['coal']; // all coal uses same unit
		
		return '';
	}
	
	function get_gtco2_per_ej( $type ){
		
		$_this = self::instance();
		$type = $_this->parseType($type);
			
		if ( !empty($_this->ej_per_unit[$type]) )
			$energy = $_this->ej_per_unit[$type];
		
		if ( !empty($_this->co2_per_unit[$type]) )
			$co2 = $_this->co2_per_unit[$type];
		
		return round( $co2/$energy, 10);	
	
	}
	
	function convert_to_energy_equivalent( $type, $quantity ){
		
		$_this = self::instance();
		$type = $_this->parseType($type);
			
		if ( !empty($_this->ej_per_unit[$type]) )
			$energy = $_this->ej_per_unit[$type] * $quantity;
		
		return round( $energy, self::DECIMALS );	
	}
	
	function convert_to_co2( $type, $quantity ){
		
		$_this = self::instance();
		$type = $_this->parseType($type);
			
		if ( !empty($_this->co2_per_unit[$type]) )
			$co2 = $_this->co2_per_unit[$type] * $quantity;
		
		elseif ( str_startswith($type, 'coal') )
			$co2 = $_this->co2_per_unit['coal'] * $quantity; // use generic coal conversion
		
		return round($co2, self::DECIMALS);
	}
	
	function convert_to_mmboe( $type, $quantity ){
		
		$_this = self::instance();
		$type = $_this->parseType($type);
			
		if ( !empty($_this->mmboe_per_unit[$type]) )
			$mmboe = $_this->mmboe_per_unit[$type] * $quantity;
		
		elseif ( str_startswith($type, 'coal') )
			$mmboe = $_this->mmboe_per_unit['coal'] * $quantity; // use generic coal conversion
		
		return round($mmboe, self::DECIMALS);
	}
	
	function get_label($type){
		
		$_this = self::instance();
		$type = $_this->parseType($type);
		
		return $_this->resources[ $type ];
	}
	
	function get_all_co2(){
		
		$_this = self::instance();
		
		return $_this->co2_per_unit;
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
	
	function parseType( $arg ){
		
		$_this = self::instance();
		
		if ( isset($_this->resources[$arg]) )
			return $arg;
		
		else if ( isset($_this->resources[ 'coal.' . $arg ]) )
			return 'coal.' . $arg;
		
	}

}
