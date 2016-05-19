<?php
/**
 * Theme template wrapper
 *
 * @link http://roots.io/an-introduction-to-the-roots-theme-wrapper/
 * @link http://scribu.net/wordpress/theme-wrappers.html
 */
namespace WordPress\Theme;

class TemplateWrapper
{
	/**
	 * Full path to the main template file
	 * @var string
	 */
	public static $main_template;

	/** 
	 * Basename of the template file; e.g. 'page' for 'page.php' etc.
	 * @var string
	 */
	public static $base;

	/**
	 * Basename of template file
	 * @var string
	 */
	public $slug;
	
	/** 
	 * Array of templates
	 * @var array
	 */
	public $templates;
	
	public $found;

	public function __construct($template = 'base.php') {
		
		$this->templates = array($template);
		$this->slug = basename($template, '.php');
		
		if (static::$base) {
			array_unshift($this->templates, sprintf('%s-%s.php', substr($template, 0, -4), static::$base));
		}
	}

	public function __toString() {
		$this->templates = apply_filters("template/{$this->slug}", $this->templates);
		$this->found = locate_template($this->templates);
		return $this->found;
	}
	
	public static function setMainTemplate($template) {
		static::$main_template = $template;
		static::$base = basename(static::$main_template, '.php');
		if (static::$base === 'index') {
			static::$base = false;
		}
	}

	public static function getMainTemplate() {
		return static::$main_template;
	}
	
	public function getBase() {
		return static::$base;
	}
	
	public static function wrap($template) {
		
		// Check for other filters returning null
		if (! is_string($template)) {
			return $template;
		}

		static::$main_template = $template;
		static::$base = basename(static::$main_template, '.php');

		if (static::$base === 'index') {
			static::$base = false;
		}

		return new TemplateWrapper();
	}
}
