<?php

$__post_type = "company";

$__singular = "Exchange";
$__singular_lc = "exchange";
$__plural = "Exchanges";
$__full = "Exchange";
$__full_pl = "Exchanges";

$domain = "default";

$labels = array(
	"name"                       => _x( $__full_pl, "Taxonomy General Name", $domain ),
	"singular_name"              => _x( $__full, "Taxonomy Singular Name", $domain ),
	"menu_name"                  => __( $__plural, $domain ),
	"all_items"                  => __( "All {$__plural}", $domain ),
	"parent_item"                => __( "Parent {$__singular}", $domain ),
	"parent_item_colon"          => __( "Parent {$__singular}:", $domain ),
	"new_item_name"              => __( "New {$__singular}", $domain ),
	"add_new_item"               => __( "Add New {$__singular}", $domain ),
	"edit_item"                  => __( "Edit {$__singular}", $domain ),
	"update_item"                => __( "Update {$__singular}", $domain ),
	"separate_items_with_commas" => __( "Separate {$__plural} with commas", $domain ),
	"search_items"               => __( "Search {$__plural}", $domain ),
	"add_or_remove_items"        => __( "Add or remove {$__plural}", $domain ),
	"choose_from_most_used"      => __( "Choose from the most used {$__plural}", $domain ),
);

$rewrite = array(
	'slug'                      => $__singular_lc,
	'with_front'				=> true,
	'hierarchical'              => true,
);

$args = array(
	"labels"                     => $labels,
	"hierarchical"               => false,
	"public"                     => true,
	"show_ui"                    => true,
	"show_admin_column"          => true,
	"show_in_nav_menus"          => true,
	"show_tagcloud"              => true,
	"query_var"                  => $__singular_lc,
	"rewrite"                    => $rewrite,
);

register_taxonomy( $__singular_lc, $__post_type, $args );