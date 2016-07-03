<?php
/**
 * Plugin Name: WP-App
 * Description: Loader for the <code>wells5609/wp-app</code> Composer package.
 * Version: 1.2
 * Author: Wells
 * License: BSD
 * Plugin URI: https://github.com/wells5609/wp-app/
 */
namespace WordPress;

/**
 * Path to the root installation directory.
 * @var string
 */
define('DOCROOT', $GLOBALS['root_dir']);

/**
 * Path to the web directory.
 * @var string
 */
define('WEBROOT', DOCROOT.'/web');

/**
 * Path to the app directory.
 * @var string
 */
define('APPROOT', DOCROOT.'/app');

/**
 * Whether the request came from the command line.
 * @var boolean
 */
define('CLI', PHP_SAPI === 'cli');

/**
 * Whether the current environment is "development".
 * @var boolean
 */
define('DEV', WP_ENV === 'development');

/**
 * ------------------------------------------------------------
 * 1) Include wp-app functions from the /wp-app/functions directory
 * 2) Include `WordPress` class
 * 3) Add WordPress namespace to autoloader
 * 4) Create the application
 * 5) Set the default services
 * 6) Set the 'app.load' event
 * ----------------------------------------------------------
 */
add_action('muplugins_loaded', function() {
	
	foreach(glob(__DIR__.'/functions/*.php') as $__file) {
		require $__file;
	}
	
	require __DIR__.'/src/WordPress.php';

	composer_autoloader()->addPsr4('WordPress\\', array(__DIR__.'/src'));
	
	/**
	 * @var \WordPress\Application\Environment $env
	 */
	$env = new Application\Environment(dirname(ABSPATH), dirname(dirname(ABSPATH)));
	
	/**
	 * Get the app class name
	 */
	$class = apply_filters('application_class', 'WordPress\App');
	
	/**
	 * Application instance.
	 *
	 * @var WordPress\App $app
	 */
	\WordPress::init($app = new $class($env));
	
	$app->set('autoloader', function (App $app) {
		return $app->getGlobal('autoloader');
	});

	$app->setShared('env', $env);

	$app->setShared('request', Http\Request::createFromGlobals());

	$app->setShared('restManager', new Rest\Manager);
	
	$app->setShared('modelManager', new Model\Manager);
	
	$app->setShared('dataManager', new Data\Manager);
	
	$app->set('post', function() {
		return Model\Post::instance();
	});

	$app->set('user', function() {
		return Model\User::instance();
	});

	$app->setShared('dbConnection', function (App $app) {
		return new Database\Connection($app->getGlobal('wpdb'));
	});

	if (CLI) {

		$app->setShared('cliRequest', function() {
			return Cli\Request::createFromGlobals();
		});

		$app->setShared('console', function (App $app) {
			return new Cli\Console($app['cliRequest']);
		});
	}

	/**
	 * ------------------------------------------------------------
	 * Set callback to load app/ files on 'app.load'
	 * ----------------------------------------------------------
	 */
	$app->on('load', function (App $app) {

		$app->get('dataManager')->register(new Data\Core\Type('post'));
		$app->get('dataManager')->register(new Data\Core\Type('page'));
		$app->get('dataManager')->register(new Data\Core\Type('revision'));
		$app->get('dataManager')->register(new Data\Core\Type('attachment'));
		$app->get('dataManager')->register(new Data\Core\Type('taxonomy'));
		$app->get('dataManager')->register(new Data\Core\Type('term'));
		$app->get('dataManager')->register(new Data\Core\Type('user'));
		
		$app->loadFile(APPROOT . '/bootstrap.php');
		$app->loadFile(APPROOT . '/bootstrap/' . (CLI ? 'cli' : 'http') . '.php');
	});
	
}, -99);
