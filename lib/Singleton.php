<?php


abstract class Singleton {
	
	static protected $_instance;
	
	static function instance(){
		if ( !isset(static::$_instance) ){
			static::$_instance = new static();	
		}
		return static::$_instance;
	}
	
}