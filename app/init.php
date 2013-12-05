<?php
$App = App::instance();
$Registry = Registry::instance();

$App->enable_feature('api')
	->enable_feature('cache');

$Registry
	->register_datatype( 'company', array(
		'has_meta' => true,
		'extends_post_type' => 'company',
	) )

	->register_datatype( 'reserve' )
	
	->register_datatype( 'Company_Reserves' );