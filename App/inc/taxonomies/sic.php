<?php

$__singular = "SIC";
$__singular_lc = "sic";
$__plural = "SICs";

$__objects = array('company');

$__domain = "default";

register_taxonomy( $__singular_lc, $__objects, array(
		
	"labels"                     => array(
		"name"                       => _x( $__plural, "Taxonomy General Name", $__domain ),
		"singular_name"              => _x( $__singular, "Taxonomy Singular Name", $__domain ),
		"menu_name"                  => __( $__plural, $__domain ),
		"all_items"                  => __( "All {$__plural}", $__domain ),
		"parent_item"                => __( "Parent {$__singular}", $__domain ),
		"parent_item_colon"          => __( "Parent {$__singular}:", $__domain ),
		"new_item_name"              => __( "New {$__singular}", $__domain ),
		"add_new_item"               => __( "Add New {$__singular}", $__domain ),
		"view_item"					 => __( "View {$__singular}", $__domain ),
		"edit_item"                  => __( "Edit {$__singular}", $__domain ),
		"update_item"                => __( "Update {$__singular}", $__domain ),
		"separate_items_with_commas" => __( "Separate {$__plural} with commas", $__domain ),
		"search_items"               => __( "Search {$__plural}", $__domain ),
		"not_found"					 => __( "No {$__plural} found", $__domain ),
		"add_or_remove_items"        => __( "Add or remove {$__plural}", $__domain ),
		"choose_from_most_used"      => __( "Choose from the most used {$__plural}", $__domain ),
	),

	"rewrite"                    => array(
		"slug"                       => $__singular_lc,
	),

	"hierarchical"               => false,
	"public"                     => true,
	"show_ui"                    => true,
	"show_admin_column"          => true,
	"show_in_nav_menus"          => true,
	"show_tagcloud"              => true,
	"query_var"                  => $__singular_lc,
	
));