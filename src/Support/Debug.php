<?php

namespace WordPress\Support;

class Debug
{
	
	/**
	 * Returns the number of database queries executed during the request.
	 * 
	 * @return int|string
	 */
	public static function queries($format = false) {
		$value = get_num_queries();
		return $format ? $value.' queries' : $value;
	}
	
	/**
	 * Returns the amount of memory used during the request.
	 * 
	 * @return float|string
	 */
	public static function memoryUsage($format = false) {
		$value = memory_get_peak_usage();
		return $format ? number_format($value/1024/1024, 3).' MB' : $value;
	}
	
	/**
	 * Returns the time elapsed since the start of the request.
	 * 
	 * @return int|float|string
	 */
	public static function timer($format = false) {
		$value = timer_stop(false, 4);
		return $format ? ($value*1000).' ms' : $value;
	}
	
	/**
	 * Returns debug statistics.
	 * 
	 * @param boolean $format [Optional] Default = false
	 * 
	 * @return array
	 */
	public static function stats($format = false) {
		return array(
			'queries' => self::queries($format),
			'memory' => self::memoryUsage($format),
			'timer' => self::timer($format)
		);
	}
		
	/**
	 * Returns or prints the debug stats as HTML.
	 * 
	 * @param boolean $display [Optional] Default = false
	 * 
	 * @return string
	 */
	public static function html($display = false) {
		$stats = self::stats(true);
		$string = '<span id="wp-debug-output"> '
			.$stats['queries'].' // '
			.$stats['memory'].' // '
			.$stats['timer']
			.'</span>';
		return $display ? print $string : $string;
	}
	
	/**
	 * Prints debug statistics HTML.
	 */
	public static function printHtml() {
		echo self::html();
	}
	
}
