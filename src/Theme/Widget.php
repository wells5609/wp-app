<?php

namespace WordPress\Theme;

abstract class Widget extends \WP_Widget
{

	/**
	 * Unique identifier for your widget.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * widget file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	abstract public function getSlug();

	abstract public function getTitle();

	public function getDescription() {
		return '';
	}

	public function getTextdomain() {
		return null;
	}

	public function getRootFileName() {
		return (new \ReflectionClass($this))->getFileName();
	}

	public function getRootPath() {
		return dirname($this->getRootFileName()).'/';
	}

	public function getTemplatePath($filename) {
		return $this->getRootPath().'/'.$filename;
	}

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function loadTextdomain() {
		load_plugin_textdomain($this->getTextdomain(), false, $this->getRootPath().'lang/');
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if
	 * WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate($network_wide) {}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU
	 * is disabled or plugin is activated on an individual blog
	 */
	public function deactivate($network_wide) {
		$this->flushCache();
	}
	
	public static function register() {
		$class = get_called_class();
		add_action('widgets_init', function() use($class) {
			register_widget($class);
		});
	}

	protected function getWidgetVars(array $instance, array $args) {
		return array_merge(array(), $args, $instance);
	}

	protected function getFormVars(array $instance) {
		return array_merge(array(), $this->getDefaultOptions(), $instance);
	}

	protected function getDefaultOptions() {
		return array();
	}

	protected function updateOptions(array $options) {
		return array_map('strip_tags', $options);
	}

	protected function getCachedOutput($id) {
		$cache = wp_cache_get($this->getSlug(), 'widget') ?: array();
		return isset($cache[$id]) ? $cache[$id] : null;
	}

	protected function setCachedOutput($id, $output) {
		$cache = wp_cache_get($this->getSlug(), 'widget') ?: array();
		$cache[$id] = $output;
		wp_cache_set($this->getSlug(), $cache, 'widget', 3600);
	}

	/**
	 * Flushes the widget cache.
	 *
	 * @return void
	 */
	public function flushCache() {
		wp_cache_delete($this->getSlug(), 'widget');
	}

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// load plugin text domain
		if ($this->getTextdomain()) {
			add_action('init', array($this, 'loadTextdomain'));
		}

		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook($this->getRootFileName(), array($this, 'activate'));
		register_deactivation_hook($this->getRootFileName(), array($this, 'deactivate'));

		parent::__construct($this->getSlug(), $this->maybeTranslate($this->getTitle()), array(
			'classname' => $this->getSlug(),
			'description' => $this->maybeTranslate($this->getDescription())
		));

		// Register admin styles and scripts
		//add_action('admin_print_styles', array($this, 'register_admin_styles'));
		//add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));

		// Register site styles and scripts
		//add_action('wp_enqueue_scripts', array($this, 'register_widget_styles'));
		//add_action('wp_enqueue_scripts', array($this, 'register_widget_scripts'));
	}

	protected function maybeTranslate($text) {
		$textdomain = $this->getTextdomain();
		return $textdomain ? __($text, $textdomain) : $text;
	}

	protected function fileOutput($__filename, array $__localvars) {
		ob_start();
		extract($__localvars, EXTR_SKIP);
		require $__filename;
		return ob_get_clean();
	}

	/** ------------------------------------------------
	 *  WP_Widget Methods
	 * ---------------------------------------------- */

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget($args, $instance) {

		if (! isset($args['widget_id'])) {
			$args['widget_id'] = $this->id;
		}

		$id = $args['widget_id'];

		// Check if there is a cached output
		if ($cached = $this->getCachedOutput($id)) {
			$output = $cached;
		} else {
			$output = isset($args['before_widget']) ? $args['before_widget'] : '';
			$output .= $this->fileOutput(
				$this->getTemplatePath('templates/widget.php'),
				$this->getWidgetVars($instance, $args)
			);
			$output .= isset($args['after_widget']) ? $args['after_widget'] : '';
			$this->setCachedOutput($id, $output);
		}

		echo $output;
	}

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 *
	 * @return array
	 */
	public function update($new_instance, $old_instance) {
		return array_merge($old_instance, $this->updateOptions($new_instance));
	}

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 *
	 * @return void
	 */
	public function form($instance) {
		$vars = wp_parse_args((array)$instance, $this->getDefaultOptions());
		echo $this->fileOutput($this->getTemplatePath('templates/admin.php'), $vars);
	}



	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {
		wp_enqueue_style($this->get_slug().'-admin-styles', plugins_url('css/admin.css', __FILE__));
	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {
		wp_enqueue_script($this->get_slug().'-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'));
	}

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {
		wp_enqueue_style($this->get_slug().'-widget-styles', plugins_url('css/widget.css', __FILE__));
	}

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {
		wp_enqueue_script($this->get_slug().'-script', plugins_url('js/widget.js', __FILE__), array('jquery'));
	}

}
