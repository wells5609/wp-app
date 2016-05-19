<?php

namespace WordPress\Theme\AddOn;

use WordPress\Theme\AddOnInterface;
use WordPress\Theme\ActiveTheme;

class GoogleAnalytics implements AddOnInterface
{
	
	protected $ga_id;
	
	public function __construct($googleAnalyticsID) {
		$this->ga_id = $googleAnalyticsID;
	}
	
	public function getName() {
		return 'google_analytics';
	}
	
	public function load(ActiveTheme $theme) {
		add_action('wp_footer', array($this, 'render'), 20);
	}
	
	/**
	 * Action callback: wp_footer
	 * Cookie domain is 'auto' configured. See: http://goo.gl/VUCHKM
	 */
	public function render() {
		$str = '<script>';
		if (WP_ENV === 'production') {
			$str .= '(function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]='
				.'function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;'
				.'e=o.createElement(i);r=o.getElementsByTagName(i)[0];'
				."e.src='//www.google-analytics.com/analytics.js';"
				."r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));";
		} else {
			$str .= "function ga() {console.log('GoogleAnalytics: ' + [].slice.call(arguments));}";
		}	
		$str .= "ga('create','".$this->ga_id."','auto');ga('send','pageview');";
		$str .= "</script>";
		echo $str;
	}
	
}
