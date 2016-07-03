<?php

namespace WordPress\Admin;

use RuntimeException;
use ReflectionClass;

class Page
{
	
	/**
	 * Page slug (required)
	 * 
	 * @var string
	 */
	protected $slug;
	
	/**
	 * Page title (required)
	 * 
	 * @var string
	 */
	protected $title;
	
	/**
	 * Minimum user capability to view page (required)
	 * 
	 * @var string
	 */
	protected $capability = 'manage_options';
	
	/**
	 * Page menu item title (optional)
	 * 
	 * @var string
	 */
	protected $menuTitle;
	
	/**
	 * Parent menu item slug (optional)
	 * 
	 * @var string
	 */
	protected $menuParent;
	
	/**
	 * Menu item icon URL (optional)
	 * 
	 * Only applies if top-level menu item.
	 * 
	 * @var string
	 */
	protected $menuIconUrl = '';
	
	/**
	 * Menu item position (optional)
	 * 
	 * Only applies if top-level menu item.
	 * 
	 * @var int
	 */
	protected $menuPosition;
	
	/**
	 * The page hook suffix name.
	 * 
	 * @var string
	 */
	protected $hookSuffix;
	
	/**
	 * Notice messages to display on next page load.
	 * 
	 * @var array
	 */
	protected $adminNotices = array();
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->register();
	}
	
	public function setSlug($slug) {
		$this->slug = $slug;
	}
	
	public function getSlug() {
		return $this->slug;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setCapability($capability) {
		$this->capability = $capability;
	}
	
	public function getCapability() {
		return $this->capability;
	}
	
	public function setMenuTitle($menuTitle) {
		$this->menuTitle = $menuTitle;
	}
	
	public function getMenuTitle() {
		return $this->menuTitle ? $this->menuTitle : $this->getTitle();
	}
	
	public function setMenuParent($parent) {
		$this->menuParent = $parent;
	}
	
	public function getMenuParent() {
		return $this->menuParent;
	}
	
	public function setMenuIconUrl($url) {
		$this->menuIconUrl = $url;
	}
	
	public function getMenuIconUrl() {
		return $this->menuIconUrl;
	}
	
	public function setMenuPosition($position) {
		$this->menuPosition = (int)$position;
	}
	
	public function getMenuPosition() {
		return $this->menuPosition;
	}
	
	public function getHookSuffix() {
		return $this->hookSuffix;
	}
	
	public function getNonceKey() {
		return constant(get_class($this).'::NONCE_KEY') ?: $this->getSlug();
	}
	
	public function showNotice($msg, $type = 'info', $alt = false) {
		$this->adminNotices[] = UI::notice($msg, $type, true, $alt);
	}
	
	public function printNotices() {
		foreach($this->adminNotices as $notice) {
			echo $notice;
		}
	}
	
	public function createNonce() {
		return wp_create_nonce($this->getNonceKey());
	}
	
	public function verifyNonce($nonce) {
		return wp_verify_nonce($nonce, $this->getNonceKey());
	}

	/**
	 * Register the page menu item.
	 */
	public function register() {
	
		$this->assertValid();
	
		add_action('admin_menu', function() {
			if ($this->getMenuParent()) {
				$this->hookSuffix = add_submenu_page(
					$this->getMenuParent(),
					$this->getTitle(),
					$this->getMenuTitle(),
					$this->getCapability(),
					$this->getSlug(),
					array($this, 'render')
					);
			} else {
				$this->hookSuffix = add_menu_page(
					$this->getTitle(),
					$this->getMenuTitle(),
					$this->getCapability(),
					$this->getSlug(),
					array($this, 'render'),
					$this->getMenuIconUrl(),
					$this->getMenuPosition()
					);
			}
		});
	}
	
	public function assertValid() {
		if (! is_admin()) {
			throw new RuntimeException("Admin pages must only be created in the administration area.");
		}
		if (! $this->getSlug() || ! $this->getTitle() || ! $this->getCapability()) {
			throw new RuntimeException("Admin page must have 'slug', 'title', and 'capability' properties.");
		}
	}
	
	public function render() {
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';
		$path = dirname((new ReflectionClass($this))->getFileName()).'/views/';
		if (file_exists($path.$action.'.php')) {
			require $path.$action.'.php';
		}
		if (file_exists($path.'admin-page.php')) {
			require $path.'admin-page.php';
		}
	}
	
}
