<?php

namespace WordPress\Theme\AddOn;

use WordPress\Theme\AddOnInterface;
use WordPress\Theme\ActiveTheme;

class JqueryCdn implements AddOnInterface
{
	
	/**
	 * jQuery version to load.
	 * @var string
	 */
	protected $version;
	
	/**
	 * Local fallback file URL.
	 * @var string
	 */
	protected $localUrl;
	
	public function __construct($version = '2.1.4') {
		$this->version = $version;
	}
	
	public function getName() {
		return 'jquery_cdn';
	}
	
	public function load(ActiveTheme $theme) {
		
		if (! is_admin()) {
			
			$scripts = wp_scripts();
			
			// Add fallback to local jQuery
			if (isset($scripts->registered['jquery-core'])) {
				$this->localUrl = get_site_url(null, $scripts->registered['jquery-core']->src);
				add_action('wp_footer', array($this, 'renderFallbackScript'), 1, -999);
			}
			
			add_action('wp_enqueue_scripts', array($this, 'enqueue'), 9999);
		}
	}
	
	public function getUrl() {
		return '//ajax.googleapis.com/ajax/libs/jquery/'.$this->version.'/jquery.min.js';
	}
	
	public function enqueue() {
		
		/**
		 * jQuery is loaded using the same method from HTML5 Boilerplate:
		 * Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
		 */
		wp_deregister_script('jquery');
		wp_register_script('jquery', $this->getUrl(), array(), null, false);
		wp_enqueue_script('jquery');
	}
	
	public function renderFallbackScript() {
		echo '<script>window.jQuery || document.write(\'<script src="'.$this->localUrl.'"><\/script>\')</script>'."\n";
	}
	
}
