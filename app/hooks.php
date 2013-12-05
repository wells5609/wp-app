<?php

add_action( 'pre_get_posts', '_pre_get_posts_company', 1 );

	function _pre_get_posts_company( $query ){
		
		if ( is_admin() || !$query->is_main_query() )
			return;
		
		if ( is_tax('industry') || is_tax('sic') || is_tax('exchange') ) {
			$query->set( 'post_type', 'company' );
			$query->set( 'posts_per_page', 50 );
			return;	
		}
		
		if ( is_post_type_archive('company') ) {
			$query->set( 'posts_per_page', 50 );
			return;	
		}	
		
	}