<?php

namespace WordPress\Plugin;

use Composer\Autoload\ClassLoader;
use RuntimeException;

class MustUsePlugins
{
	
	const OPTION_KEY = 'autoloaded_muplugins';
	
	/**
	 * Stores our plugin cache and site option.
	 * 
	 * @var array
	 */
	private $cache;
	
	/**
	 * Contains the autoloaded plugins (only when needed).
	 * 
	 * @var array
	 */
	private $auto_plugins;
	
	/**
	 * Contains the mu plugins (only when needed).
	 * 
	 * @var array
	 */
	private $mu_plugins;
	
	/**
	 * Contains the plugin count.
	 * 
	 * @var int
	 */
	private $count;
	
	/**
	 * Newly activated plugins.
	 * 
	 * @var array
	 */
	private $activated;
	
	/**
	 * Relative path to the mu-plugins dir.
	 * 
	 * @var string
	 */
	private $relative_path;
	
	/**
	 * @var \Composer\Autoload\ClassLoader
	 */
	private $autoloader;
	
	/**
	 * Whether the MU plugins have been loaded.
	 * 
	 * @var boolean
	 */
	private static $loaded = false;
	
	/**
	 * Loads the MU plugins.
	 * 
	 * @param \Composer\Autoload\ClassLoader $loader
	 * 
	 * @return \WordPress\Plugin\MustUsePlugins
	 */
	public static function load(ClassLoader $loader) {
		return new self($loader);
	}
	
	/**
	 * Loads the MU plugins.
	 * 
	 * @param \Composer\Autoload\ClassLoader $loader
	 */
	private function __construct(ClassLoader $loader) {
		
		if (self::$loaded) {
			throw new RuntimeException("MU plugins have already been loaded.");
		}
		
		$this->autoloader = $loader;
		$this->relative_path = '/../'.basename(WPMU_PLUGIN_DIR);
		
		add_filter('extra_plugin_headers', function (array $headers) {
			$headers[] = 'Namespace';
			$headers[] = 'Autoload';
			return $headers;
		});
		
		if (is_admin()) {
			add_filter('show_advanced_plugins', array($this, 'filterShowAdvancedPlugins'), 0, 2);
			add_filter('plugin_row_meta', array($this, 'filterPluginRowMeta'), 0, 4);
		}
		
		$this->loadCache();
		$this->validatePlugins();
		$this->countPlugins();
		$this->loadPlugins();
		$this->runActivationHooks();
		
		self::$loaded = true;
	}

	/**
	 * Filter show_advanced_plugins to display the autoloaded plugins.
	 * 
	 * @param boolean $bool
	 * @param string $type
	 * 
	 * @return boolean
	 */
	public function filterShowAdvancedPlugins($bool, $type) {

		if (
			$type != 'mustuse'
			|| get_current_screen()->base != (is_multisite() ? 'plugins-network' : 'plugins')
			|| ! current_user_can('activate_plugins')
		) {
			return $bool;
		}
		
		// May as well update the transient cache whilst here.
		$this->updateCache();

		$this->auto_plugins = array_map(function ($plugin) {
			$plugin['Name'] .= ' *';
			return $plugin;
		}, $this->auto_plugins);

		$GLOBALS['plugins']['mustuse'] = array_unique(array_merge($this->auto_plugins, $this->mu_plugins), SORT_REGULAR);

		// Prevent WordPress overriding our work.
		return false;
	}

	/**
	 * Filter the array of row meta for each plugin in the Plugins list table.
	 *
	 * @param array  $meta	 An array of the plugin's metadata.
	 * @param string $file	 Path to the plugin file, relative to the plugins directory.
	 * @param array  $data	 An array of plugin data.
	 * @param string $status Status of the plugin. Defaults are 'All', 'Active', 'Inactive', 
	 * 						 'Recently Activated', 'Upgrade', 'Must-Use', 'Drop-ins', 'Search'.
	 * 
	 * @return array
	 */
	public function filterPluginRowMeta(array $meta, $file, $data, $status) {
		if ($status === 'mustuse') {
			if (isset($data['Autoload']) && isset($data['Namespace'])) {	
				if (strlen($data['Autoload']) && strlen($data['Namespace'])) {
					$meta[] = 'Namespace <code>'.$data['Namespace'].'</code>';
					$meta[] = 'PSR-4 Path <code>'.dirname($file).'/'.trim($data['Autoload'], '/\\').'</code>';
				}
			}
		}
		return $meta;
	}

	/**
	 * This sets the cache or calls for an update.
	 */
	private function loadCache() {
		$cache = get_site_option(self::OPTION_KEY);
		if ($cache === false) {
			return $this->updateCache();
		}
		$this->cache = $cache;
	}

	/**
	 * Get the plugins and mu-plugins from the mu-plugin path and remove duplicates.
	 * 
	 * Check cache against current plugins for newly activated plugins.
	 * 
	 * Then we can update the cache (array stored as an option).
	 */
	private function updateCache() {
		
		require_once ABSPATH.'wp-admin/includes/plugin.php';

		$this->auto_plugins = get_plugins($this->relative_path);
		$this->mu_plugins = get_mu_plugins();
		
		$plugins = array_diff_key($this->auto_plugins, $this->mu_plugins);
		
		$this->activated = is_array($this->cache['plugins']) 
			? array_diff_key($plugins, $this->cache['plugins']) 
			: $plugins;
		
		$this->cache = array(
			'plugins' => $plugins,
			'count' => $this->countPlugins()
		);

		update_site_option(self::OPTION_KEY, $this->cache);
	}

	/**
	 * Check that the plugin file exists, if it doesn't update the cache.
	 */
	private function validatePlugins() {
		foreach ($this->cache['plugins'] as $filename => $_) {
			if (! file_exists(WPMU_PLUGIN_DIR.'/'.$filename)) {
				$this->updateCache();
				break;
			}
		}
	}

	/**
	 * Count our plugins (but only once) by counting the top level folders in the
	 * mu-plugins dir. If it's more or less than last time, update the cache.
	 */
	private function countPlugins() {
		if (! isset($this->count)) {
			$count = count(glob(WPMU_PLUGIN_DIR.'/*/', GLOB_ONLYDIR|GLOB_NOSORT));
			if (! isset($this->cache['count']) || $count != $this->cache['count']) {
				$this->count = $count;
				$this->updateCache();
			}
		}
		return $this->count;
	}
	
	/**
	 * Loads the plugin files using require_once.
	 */
	private function loadPlugins() {
		foreach ($this->cache['plugins'] as $filename => $info) {
			$this->autoloadPlugin($filename, $info);
			$this->includePlugin($filename);
		}
	}
	
	/**
	 * Registers a plugin's PSR-4 path with the class loader.
	 * 
	 * @param string $filename
	 * @param array $info
	 */
	private function autoloadPlugin($filename, array $info) {
		if (! empty($info['Namespace']) && ! empty($info['Autoload'])) {
			$path = WPMU_PLUGIN_DIR.'/'.dirname($filename).'/'.trim($info['Autoload'], '/\\');
			$this->autoloader->addPsr4($info['Namespace'], array($path));
		}
	}
	
	/**
	 * Includes a MU plugin file.
	 * 
	 * @param string $filename
	 */
	private function includePlugin($filename) {
		require_once WPMU_PLUGIN_DIR.'/'.$filename;
	}
	
	/**
	 * This runs the plugin activation hooks that would be run if the plugins were
	 * loaded as usual. 
	 * 
	 * Since MU plugins are removed by deletion, there is no way to run the 
	 * deactivatation or uninstall hooks.
	 */
	private function runActivationHooks() {
		if (! empty($this->activated)) {
			foreach ($this->activated as $filename => $_) {
				do_action('activate_'.$filename);
			}
		}
	}

}
