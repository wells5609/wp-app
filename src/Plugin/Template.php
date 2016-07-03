<?php

namespace WordPress\Plugin;

abstract class Template
{

	/**
	 * Unique identifier for the plugin.
	 *
	 * @var string
	 */
	protected $slug;
	
	protected $admin_page_title = '';
	protected $admin_menu_title = '';
	protected $admin_menu_parent_slug = '';
	protected $admin_page_capability = 'manage_options';
	
	/**
	 * Plugin directory path.
	 * 
	 * @var string
	 */
	private $path;
	
	/**
	 * Plugin directory URL.
	 * 
	 * @var string
	 */
	private $url;

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Sets the plugin directory path and URL, loads localization files, 
	 * and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {
		
		$this->path = plugin_dir_path(__FILE__);
		$this->url  = plugin_dir_url(__FILE__);
		$this->slug = $this->slug ?: basename($this->path);
		
		load_plugin_textdomain($this->slug, false, $this->path.'lang/');

		// Hooks fired when the plugin is activated and deactivated
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
		
		if (is_admin()) {
				
			add_action('init', array($this, 'admin_init'));
			
			// Register admin menu page	
			if ($this->admin_page_title && $this->admin_menu_parent_slug) {
				add_action('admin_menu', array($this, 'add_submenu_page'));
			}
			
			$filename = pathinfo(__FILE__, PATHINFO_FILENAME);
			
			add_filter("plugin_action_links_{$this->slug}/{$filename}", array($this, 'plugin_action_links'));
			
		} else {
			add_action('init', array($this, 'init'));
		}
		
		// Register admin styles and scripts
		add_action('admin_print_styles', array($this, 'admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

		// Register site styles and scripts
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		
		$this->load();
	}
	
	public function init() {
		
	}
	
	public function admin_init() {
		
	}
	
	public function admin_page() {
		
	}
	
	public function add_submenu_page() {
		
		$this->admin_page = add_submenu_page(
			$this->admin_menu_parent_slug, 
			$this->admin_page_title, 
			empty($this->admin_menu_title) ? $this->admin_page_title : $this->admin_menu_title, 
			$this->admin_page_capability, 
			$this->slug, 
			array($this, 'admin_page')
		);
	}
	
	/**
	 * Return the widget slug.
	 *
	 * @return string Plugin slug.
	 */
	final public function get_slug() {
		return $this->slug;
	}
	
	final public function get_path($path) {
		
		if ($path) {
			return $this->path.ltrim($path, '/\\');
		}
		
		return $this->path;
	}
	
	final public function get_url($path = '') {
		
		if ($path) {
			return $this->url.ltrim($path, '/\\');
		}
		
		return $this->url;
	}

	/*--------------------------------------------------*/
	/* Protected Functions
	/*--------------------------------------------------*/

	/**
	 * Called at end of constructor
	 */
	protected function load() {

	}

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if
	 * WPMU is disabled or plugin is activated on an individual blog.
	 * 
	 * @return void
	 */
	public function activate($network_wide) {
		
	}
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU
	 * is disabled or plugin is activated on an individual blog.
	 * 
	 * @return void
	 */
	public function deactivate($network_wide) {
		
	}
	
	/**
	 * Registers and enqueues admin-specific styles.
	 * 
	 * @return void
	 */
	public function register_admin_styles() {
		wp_enqueue_style($this->slug.'-admin-styles', $this->get_url('css/admin.css'));
	}
	
	/**
	 * Registers and enqueues admin-specific JavaScript.
	 * 
	 * @return void
	 */
	public function register_admin_scripts() {
		wp_enqueue_script($this->slug.'-admin-script', $this-get_url('js/admin.js'), array('jquery'));
	}
	
	/**
	 * Registers and enqueues front-end styles.
	 * 
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->slug.'-widget-styles', $this->get_url('css/widget.css'));
	}
	
	/**
	 * Registers and enqueues front-end scripts.
	 * 
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->slug.'-script', $this->get_url('js/widget.js'), array('jquery'));
	}
	
}
