<?php
/** Cache */

function hack_of_an_object_cache_init(){
	
	if ( function_exists('xcache_get') )
		$engine = 'xcache';
	else if ( function_exists('apc_fetch') )
		$engine = 'apc';
	else
		$engine = 'wp';
	
	define( 'APP_CACHE_ENGINE', $engine );
	define( 'APP_CACHE_PREFIX', sha1($_SERVER['HTTP_HOST']) . '|' );
	define( 'APP_CACHE_DEFAULT_TTL', 1800 );
	define( 'APP_CACHE_SERIALIZER', function_exists('igbinary_serialize') ? 'igbinary_serialize' : 'serialize' );
	define( 'APP_CACHE_UNSERIALIZER', function_exists('igbinary_unserialize') ? 'igbinary_unserialize' : 'unserialize' );
}

function cache_get_prefix( $group = 'default' ){
	
	return APP_CACHE_PREFIX . $group . '|';
}

function cache_isset( $id, $group = 'default' ){
	
	if ( 'xcache' === APP_CACHE_ENGINE ){
		
		return xcache_isset( cache_get_prefix($group) . $id );	
	}
	return wp_cache_get( $id, $group ) ? true : false;
}

function cache_get( $id, $group = 'default' ){
	
	if ( 'xcache' === APP_CACHE_ENGINE ){
		
		$value = xcache_get( cache_get_prefix($group) . $id );	
		
		if ( is_serialized($value) ){
			$unserializer = APP_CACHE_UNSERIALIZER;
			$value = $unserializer($value);
		}
		return $value;
	}
	return wp_cache_get( $id, $group );
}

function cache_set( $id, $value, $group = 'default', $ttl = APP_CACHE_DEFAULT_TTL ){
	
	if ( 'xcache' === APP_CACHE_ENGINE ){
		
		if ( is_object($value) ){
			$serializer = APP_CACHE_SERIALIZER;
			$value = $serializer( $value );
		}
		return xcache_set( cache_get_prefix($group) . $id, $value, $ttl );	
	}
	return wp_cache_set( $id, $value, $group, ($ttl === APP_CACHE_DEFAULT_TTL)? 0 : $ttl );
}

function cache_unset( $id, $group = 'default' ){
	
	if ( 'xcache' === APP_CACHE_ENGINE ){
		
		return xcache_unset( cache_get_prefix($group) . $id );	
	}
	return wp_cache_delete( $id, $group );
}

function cache_incr( $key, $val = 1, $group = 'default', $ttl = APP_CACHE_DEFAULT_TTL ){
	
	if ( 'xcache' === APP_CACHE_ENGINE ){
		
		return xcache_inc( cache_get_prefix($group) . $key, $val, $ttl );	
	}
	return wp_cache_incr( $key, $value, $group, ($ttl === APP_CACHE_DEFAULT_TTL)? 0 : $ttl );
}

function cache_decr( $key, $val = 1, $group = 'default', $ttl = APP_CACHE_DEFAULT_TTL ){
	
	if ( 'xcache' === APP_CACHE_ENGINE ){
		
		return xcache_dec( cache_get_prefix($group) . $key, $val, $ttl );	
	}
	return wp_cache_decr( $key, $value, $group, ($ttl === APP_CACHE_DEFAULT_TTL)? 0 : $ttl );
}

function cache_flush( ){
	if ( 'xcache' === APP_CACHE_ENGINE ){
		
		return xcache_unset_by_prefix( APP_CACHE_PREFIX );	
	}
	return wp_cache_flush();
}

function cache_flush_group( $group ){
			
	$prefix = cache_get_prefix($group);
	
	if ( 'xcache' === APP_CACHE_ENGINE ){
		
		return xcache_unset_by_prefix( $prefix );	
	}
	return false;
}


/** wp cache */
if ( function_exists('wp_cache_init') ){
	hack_of_an_object_cache_init();
}
else {
		
	function wp_cache_init(){
		return hack_of_an_object_cache_init();
	}
	function wp_cache_flush(){
		return cache_flush();	
	}
	function wp_cache_get( $key, $group = 'default' ){
		return cache_get( $key, $group );	
	}
	function wp_cache_set( $key, $value, $group = 'default', $expire = APP_CACHE_DEFAULT_TTL ){
		return cache_set($key, $value, $group, $expire);	
	}
	function wp_cache_add( $key, $value, $group = 'default', $expire = APP_CACHE_DEFAULT_TTL ){
		if ( !cache_isset($key, $group) ){
			cache_set($key, $value, $group, $expire);	
			return true;
		}
		return false;
	}
	function wp_cache_incr( $key, $offset = 1, $group = 'default' ){
		return cache_incr( $key, $offset, $group);
	}
	function wp_cache_decr( $key, $offset = 1, $group = 'default' ){
		return cache_deccr( $key, $offset, $group);
	}
	function wp_cache_close(){
		return true;	
	}
	function wp_cache_delete( $key, $group = 'default' ){
		return cache_unset( $key, $group );	
	}

}