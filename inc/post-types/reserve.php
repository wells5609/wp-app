<?php
/** Post-type: reserve */

$domain = 'default';

$__singular_lc = 'reserve';
$__plural_lc = 'reserves';

$__singular = 'Reserve';
$__plural = 'Reserves';


register_post_type( $__singular_lc, array(
	
	'label'               => __( $__singular_lc, $domain ),
	'description'         => __( 'Hydrocarbon reserves.', $domain ),
	
	'labels'              => array(
		'name'                => _x( $__plural, 'Post Type General Name', $domain ),
		'singular_name'       => _x( $__singular, 'Post Type Singular Name', $domain ),
		'menu_name'           => __( $__plural, $domain ),
		'parent_item_colon'   => __( "Parent {$__singular}:", $domain ),
		'all_items'           => __( "All {$__plural}", $domain ),
		'view_item'           => __( "View {$__singular}", $domain ),
		'add_new_item'        => __( "Add New {$__singular}", $domain ),
		'add_new'             => __( "New {$__singular}", $domain ),
		'edit_item'           => __( "Edit {$__singular}", $domain ),
		'update_item'         => __( "Update {$__singular}", $domain ),
		'search_items'        => __( "Search {$__plural}", $domain ),
		'not_found'           => __( "No {$__plural} found", $domain ),
		'not_found_in_trash'  => __( "No {$__plural} found in Trash", $domain )
	),
	
	'supports'            => array( 'title', 'author', 'revisions', 'page-attributes' ),
	
	'taxonomies'          => array(),
	
	'hierarchical'        => true,
	'public'              => true,
	'show_ui'             => true,
	'show_in_menu'        => true,
	'show_in_nav_menus'   => false,
	'show_in_admin_bar'   => true,
	'menu_position'       => 9,
	'menu_icon'           => plugins_url('/icons/co2.png', __FILE__),
	'can_export'          => true,
	'has_archive'         => true,
	'exclude_from_search' => false,
	'publicly_queryable'  => true,
	'query_var'           => $__singular_lc,
	
	'rewrite'             => array(
		'slug'				=> $__singular_lc,
		'with_front'   		=> false,
		'pages'         	=> true,
		'feeds'         	=> true
	),
	
	'capability_type'     => 'post',
	//'capabilities'		  => $caps

));
