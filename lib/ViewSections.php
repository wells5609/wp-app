<?php

class ViewSections {
	
	static $string = '';
	
	static $_instance;
	
	static function instance(){
		if ( !isset(self::$_instance) ){
			self::$_instance = new self();	
		}	
		return self::$_instance;
	}
	
	public function createTable($args=null){
		return null !== $args ? new HTML_Table($args) : new HTML_Table();
	}
	
	public function s( $str ){
		$_this = self::instance();
		self::$string .= $str;
		return $_this;
	}
	
	public function output( $echo = true ){
		if ( $echo )
			echo self::$string;
		return self::$string;
	}
	
}
