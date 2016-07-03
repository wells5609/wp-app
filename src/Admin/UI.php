<?php

namespace WordPress\Admin;

class UI
{
	
	public static function screenReaderText($text, $tag = 'span') {
		return '<'.$tag.' class="screen-reader-text">'.__($text).'</'.$tag.'>';
	}
	
	public static function submitButton($text = 'Save Changes', $type = 'primary', $name = 'submit', $wrap = false) {
		return get_submit_button($text, $type, $name, $wrap);
	}
	
	public static function dismissButton($screenReaderText = 'Dismiss this notice.') {
		return '<button type="button" class="notice-dismiss">'.static::screenReaderText($screenReaderText).'</button>';
	}
	
	public static function notice($content, $type = 'info', $dismiss = true, $alt = false) {
		
		$after_p = '';
		$classes = array(
			'notice',
			"notice-$type",
		);
		
		if ($dismiss) {
			$classes[] = 'is-dismissible';
			$after_p = self::dismissButton();
		}
		
		if ($alt) {
			$classes[] = 'notice-alt';
		}
		
		return '<div class="'.implode(' ', $classes).'"><p>'.$content.'</p>'.$after_p.'</div>';
	}
	
}