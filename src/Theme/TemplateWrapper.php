<?php

namespace WordPress\Theme;

/**
 * Theme template wrapper
 *
 * @link http://roots.io/an-introduction-to-the-roots-theme-wrapper/
 * @link http://scribu.net/wordpress/theme-wrappers.html
 */
class TemplateWrapper
{
	
	const BASE_TEMPLATE_FILENAME = 'base.php';
	
	/**
	 * Full path to the main template file
	 * 
	 * @var string
	 */
	protected static $main;

	/** 
	 * Basename of the template file; e.g. 'page' for 'page.php' etc.
	 * 
	 * @var string
	 */
	protected static $basename;

	/**
	 * Array of possible templates.
	 *
	 * @var array
	 */
	protected $templates;
	
	/**
	 * Basename of the template file.
	 * 
	 * @var string
	 */
	protected $slug;
	
	/**
	 * The located template file.
	 * 
	 * @var string
	 */
	protected $found;

	/**
	 * Constructor.
	 * 
	 * @param string $template [Optional]
	 */
	public function __construct($template = self::BASE_TEMPLATE_FILENAME) {
		$this->setTemplate($template);
	}
	
	/**
	 * Sets the template file for this wrapper.
	 * 
	 * @param string $template
	 */
	public function setTemplate($template) {
	
		$this->slug = basename($template, '.php');
		
		if (static::$basename) {
			$this->templates = array(
				sprintf('%s-%s.php', substr($template, 0, -4), static::$basename), 
				$template,
			);
		} else {
			$this->templates = array($template);
		}
	}
	
	/**
	 * Locates the template file.
	 * 
	 * @return string
	 */
	public function locate() {
		if (! isset($this->found)) {
			$this->templates = apply_filters('template/'.$this->slug, $this->templates);
			$this->found = locate_template($this->templates);
		}
		return $this->found;
	}
	
	/**
	 * Returns the template file path.
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->locate();
	}
	
	/**
	 * Sets the main template for the current request.
	 * 
	 * @param string $template
	 */
	public static function setMainTemplate($template) {
		static::$main = $template;
		$basename = basename($template, '.php');
		static::$basename = ($basename === 'index') ? false : $basename;
	}
	
	/**
	 * Returns the main template file path.
	 * 
	 * @return string
	 */
	public static function getMainTemplate() {
		return static::$main;
	}
	
	/**
	 * Returns the main template's basename.
	 * 
	 * @return string
	 */
	public static function getMainTemplateBasename() {
		return static::$basename;
	}
	
	/**
	 * Outputs the template content.
	 * 
	 * @return void
	 */
	public static function render() {
		load_template(static::$main);
	}
	
	/**
	 * Returns the template content as a string.
	 * 
	 * @return string
	 */
	public static function getContents() {
		ob_start();
		static::render();
		return ob_get_clean();
	}
	
	/**
	 * Sets as the given file as the main template and returns a new wrapper.
	 * 
	 * @param string|mixed $template
	 * 
	 * @return mixed|\WordPress\Theme\TemplateWrapper
	 */
	public static function wrap($template) {
		if (is_string($template)) {
			static::setMainTemplate($template);
			return new static();
		}
		return $template;
	}
}
