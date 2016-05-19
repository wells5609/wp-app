<?php

namespace WordPress\Theme;

interface AddOnInterface
{
	
	/**
	 * Returns the add-on's name.
	 * 
	 * @return string
	 */
	public function getName();
	
	/**
	 * Loads the add-on.
	 * 
	 * @param \WordPress\Theme\ActiveTheme $theme
	 */
	public function load(ActiveTheme $theme);
	
}
