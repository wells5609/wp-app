<?php
/**
* Non-critical App-specific constants.
* Run before default-constants.php if MU
*/

// WP-like constant (e.g. OBJECT, ARRAY_N) to return only object value (e.g. meta_value)
if ( !defined('VALUE') ){
	define('VALUE', 'VALUE');
}

// unknown WP constant? getting errors in ajax calls, v3.8a
if ( !defined('SCRIPT_DEBUG') ){
	define('SCRIPT_DEBUG', false);
}
	
/* WP Overwrites */

/**
* Cookies without WordPress in name
* (MU only)
*/
if ( !defined( 'COOKIEHASH' ) ) {
	$siteurl = get_site_option( 'siteurl' );
	if ( $siteurl )
		define( 'COOKIEHASH', sha1($siteurl) );
	else
		define( 'COOKIEHASH', '' );
}
if ( !defined('USER_COOKIE') ){
	define('USER_COOKIE', 'AppUser_' . COOKIEHASH);
}
if ( !defined('PASS_COOKIE') ){
	define('PASS_COOKIE', 'AppPass_' . COOKIEHASH);
}
if ( !defined('AUTH_COOKIE') ){
	define('AUTH_COOKIE', 'App_' . COOKIEHASH);
}
if ( !defined('SECURE_AUTH_COOKIE') ){
	define('SECURE_AUTH_COOKIE', 'AppS_' . COOKIEHASH);
}
if ( !defined('LOGGED_IN_COOKIE') ){
	define('LOGGED_IN_COOKIE', 'LoggedIn_' . COOKIEHASH);
}
if ( !defined('TEST_COOKIE') ){
	define('TEST_COOKIE', 'TastyTestCookie');
}
