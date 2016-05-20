<?php

namespace WordPress\Theme\AddOn;

use WordPress\Theme\AddOnInterface;
use WordPress\Theme\ActiveTheme;

class DebugStats implements AddOnInterface
{
	
	public function getName() {
		return 'debug_stats';
	}
	
	public function load(ActiveTheme $theme) {
		add_action('wp_footer', 'WordPress\Support\Debug::printHtml', PHP_INT_MAX);
	}
	
}
