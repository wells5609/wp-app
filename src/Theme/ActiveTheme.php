<?php

namespace WordPress\Theme;

abstract class ActiveTheme implements \ArrayAccess
{
	
	/**
	 * Path to theme root directory.
	 * @var string
	 */
	protected $path;
	
	/**
	 * The sidebar instance.
	 * @var \WordPress\Theme\Sidebar
	 */
	protected $sidebar;
	
	/**
	 * Main template file.
	 * @var string
	 */
	protected $template;
	
	/**
	 * Theme add-ons.
	 * @var array
	 */
	protected $addons = array();
	
	/**
	 * Theme settings.
	 * @var array
	 */
	protected $settings = array(
		'sidebar_class' => 'WordPress\\Theme\\Sidebar',
		'wrapper_class' => 'WordPress\\Theme\\TemplateWrapper',
		'before_widget' => '<div class="widget %1$s %2$s">',
		'after_widget' => '</div>',
		'before_widget_title' => '<h3 class="widget-title">',
		'after_widget_title' => '</h3>',
	);
	
	/**
	 * Loads the theme.
	 */
	public function __construct() {
		
		$this->path = get_stylesheet_directory();
		$this->sidebar = $this->newClass($this->settings['sidebar_class']);
		
		add_filter('template_include', function ($template) {
			// Check for other filters returning null
			if (! is_string($template)) {
				return $template;
			}
			$this->template = $template;
			$class = $this->settings['wrapper_class'];
			call_user_func($class.'::setMainTemplate', $this->template);
			return $this->newClass($class);
		}, 99);
		
		if (method_exists($this, 'setup')) {
			add_action('after_setup_theme', array($this, 'setup'));
		}
		
		if (method_exists($this, 'widgets')) {
			add_action('widgets_init', array($this, 'widgets'));
		}
		
		if (method_exists($this, 'assets')) {
			add_action('wp_enqueue_scripts', array($this, 'assets'), 100);
		}
	}
	
	/**
	 * Returns a setting value.
	 * 
	 * @param string $setting
	 * @return mixed|null
	 */
	public function offsetGet($setting) {
		return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
	}
	
	/**
	 * Checks if a setting exists.
	 * 
	 * @param string $setting
	 * @return boolean
	 */
	public function offsetExists($setting) {
		return isset($this->settings[$setting]);
	}
	
	/**
	 * Sets a setting value.
	 * 
	 * @param string $setting
	 * @param mixed $value
	 */
	public function offsetSet($setting, $value) {
		$this->settings[$setting] = $value;
	}
	
	/**
	 * Deletes a setting value.
	 * 
	 * @param string $setting
	 */
	public function offsetUnset($setting) {
		unset($this->settings[$setting]);
	}
	
	/**
	 * Overload to allow access to settings via properties.
	 * 
	 * @param string $setting
	 * @return mixed
	 */
	public function __get($setting) {
		return $this->offsetGet($setting);
	}
	
	/**
	 * Adds and loads the given add-on.
	 * 
	 * @param \WordPress\Theme\AddOnInterface $addon
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function loadAddOn(AddOnInterface $addon) {
		$this->addons[$addon->getName()] = $addon;
		$addon->load($this);
		return $this;
	}
	
	/**
	 * Checks whether the theme has the given add-on.
	 * 
	 * @param string|\WordPress\Theme\AddOnInterface $addon
	 * @return boolean
	 */
	public function hasAddOn($addon) {
		if (is_object($addon)) {
			return in_array($addon, $this->addons, true);
		}
		return isset($this->addons[$addon]);
	}
	
	/**
	 * Returns an add-on by name.
	 * 
	 * @param string $addon
	 * @return \WordPress\Theme\AddOnInterface
	 */
	public function getAddOn($addon) {
		return isset($this->addons[$addon]) ? $this->addons[$addon] : null;
	}
	
	/**
	 * Returns the array of theme add-ons.
	 * 
	 * @return array
	 */
	public function getAddOns() {
		return $this->addons;
	}
	
	/**
	 * Returns the path to a template part.
	 * 
	 * @param string $path
	 * @return string
	 */
	public function getTemplatePartPath($path) {
		$path = '/'.ltrim($path, '/');
		if (isset($this->settings['partials_dirname'])) {
			return $this->settings['partials_dirname'].$path;
		}
		return $path;
	}
	
	/**
	 * Sets the template part directory name.
	 * 
	 * @param string $dirname
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function setTemplatePartDirectory($dirname) {
		$this->settings['partials_dirname'] = trim($dirname, '/');
		return $this;
	}
	
	/**
	 * Adds support for a feature.
	 * 
	 * Alias of add_theme_support()
	 * 
	 * @param string $feature
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function support($feature) {
		add_theme_support($feature);
		return $this;
	}
	
	/**
	 * Checks whether the theme supports the given feature.
	 * 
	 * Alias of current_theme_supports()
	 * 
	 * @param string $feature
	 * @return boolean
	 */
	public function supports($feature) {
		return current_theme_supports($feature);
	}
	
	/**
	 * Adds support for the given post formats.
	 * 
	 * @param array $formats
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function supportFormats(array $formats) {
		add_theme_support('post-formats', $formats);
		return $this;
	}
	
	/**
	 * Adds support for the given HTML5 elements.
	 * 
	 * @param array $elements
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function supportHtml5(array $elements) {
		add_theme_support('html5', $elements);
		return $this;
	}
	
	/**
	 * Registers an array of nav menus.
	 * 
	 * Alias of register_nav_menus()
	 * 
	 * @param array $menus
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function addMenus(array $menus) {
		register_nav_menus($menus);
		return $this;
	}
	
	/**
	 * Sets the post thumbnail size.
	 * 
	 * Alias for add_theme_support('post-thumbnails'); set_post_thumbnail_size();
	 * 
	 * @param int $width
	 * @param int $height
	 * @param boolean $crop [Optional] Default = true
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function setThumbnailSize($width, $height, $crop = true) {
		$this->support('post-thumbnails');
		set_post_thumbnail_size($width, $height, $crop);
		return $this;
	}
	
	/**
	 * Adds an image size.
	 * 
	 * Alias of add_image_size()
	 * 
	 * @param string $name
	 * @param int $width
	 * @param int $height
	 * @param boolean $crop [Optional] Default = true
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function addImageSize($name, $width, $height, $crop = true) {
		add_image_size($name, $width, $height, $crop);
		return $this;
	}
	
	/**
	 * Returns the content width.
	 * 
	 * @return int
	 */
	public function getContentWidth() {
		return $GLOBALS['content_width'];
	}
	
	/**
	 * $content_width is a global variable used by WordPress for max image upload sizes
	 * and media embeds (in pixels).
	 *
	 * Example: If the content area is 640px wide, set $content_width = 620; so images and videos will
	 * not overflow.
	 * Default: 1140px is the default Bootstrap container width.
	 * 
	 * @param int $width
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function setContentWidth($width) {
		$GLOBALS['content_width'] = (int)$width;
		return $this;
	}
	
	/**
	 * Returns the sidebar instance.
	 * 
	 * @return \WordPress\Theme\Sidebar
	 */
	public function getSidebar() {
		return $this->sidebar;
	}
	
	/**
	 * Checks whether to display the sidebar on the current page.
	 * 
	 * @return boolean
	 */
	public function displaySidebar() {
		if (! isset($this->displaySidebar)) {
			$this->displaySidebar = $this->sidebar->check();
		}
		return $this->displaySidebar;
	}
	
	/**
	 * Returns the sidebar classname.
	 * 
	 * @return string
	 */
	public function getSidebarClass() {
		return $this->settings['sidebar_class'];
	}
	
	/**
	 * Sets the sidebar classname.
	 * 
	 * @param string $class
	 * 
	 * @return \WordPress\Theme\ActiveTheme
	 */
	public function setSidebarClass($class) {
		$this->settings['sidebar_class'] = $class;
		return $this;
	}
	
	/**
	 * Returns the main template path
	 * 
	 * @return string
	 */
	public function getMainTemplate() {
		return $this->template;
	}
	
	/**
	 * Create a new TemplateWrapper
	 * 
	 * @param string $template
	 * 
	 * @return \WordPress\Theme\TemplateWrapper
	 */
	public function wrapTemplate($template) {
		return $this->newClass($this->settings['wrapper_class'], $template);
	}
	
	/**
	 * Make theme available for translation by loading a .pot file in the given directory.
	 * 
	 * Alias of load_theme_textdomain()
	 * 
	 * @param string $template
	 * 
	 * @return \WordPress\Theme\TemplateWrapper
	 */
	public function loadTextdomain($slug, $dir = '/lang') {
		load_theme_textdomain($slug, $this->path.'/'.trim($dir, '/\\'));
		return $this;
	}
	
	/**
	 * Registers a sidebar.
	 * 
	 * @param string $name
	 * @param string $id [Optional]
	 */
	protected function addSidebar($name, $id = null) {
		register_sidebar(array(
			'name' => $name,
			'id' => isset($id) ? $id : str_replace(array('-', ' '), '', $name),
			'before_widget' => $this->settings['before_widget'],
			'after_widget' => $this->settings['after_widget'],
			'before_title' => $this->settings['before_widget_title'],
			'after_title' => $this->settings['after_widget_title'],
		));
	}
	
	/**
	 * Creates a class
	 */
	protected function newClass($class, $arg = null) {
		return isset($arg) ? new $class($arg) : new $class();
	}
			
}