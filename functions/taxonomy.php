<?php

/**
 * Returns an array of the core WordPress taxonomy names.
 *
 * @return array
 */
function wp_get_builtin_taxonomies() {
	return array(
		'category',
		'post_tag',
		'post_format',
		'nav_menu',
		'link_category'
	);
}

/**
 * Returns true if taxonomy is custom.
 *
 * @param string $taxonomy Taxonomy name.
 *
 * @return boolean True if taxonomy is custom, otherwise false.
 */
function is_taxonomy_custom($taxonomy) {

	if (! taxonomy_exists($taxonomy)) {
		return false;
	}

	return ! in_array($taxonomy, wp_get_builtin_taxonomies());
}

/**
 * Returns the category term object for a post.
 * 
 * @param int|object|null $post [Optional] Post object or ID.
 * 
 * @return object Category term object.
 */
function get_post_category($post = null) {
	
	if (isset($post) && is_object($post)) {
		$post = $post->ID;
	}
	
	$cats = get_the_category($post);
	
	if (empty($cats) || ! is_array($cats)) {
		return null;
	}
	
	return reset($cats);
}

/**
 * Returns true if term is a top-level term.
 * Behavior is the same for non-hierarchical taxonomies (i.e. always returns true).
 * 
 * @param object|int|string $term Term object, ID, or slug. Can leave null if on a term page.
 * @param string $taxonomy Taxonomy name.
 * @return boolean True if term is a top-level term, otherwise false.
 */
function is_term_top_level($term = null, $taxonomy = null) {
	
	if (empty($term) || ! is_object($term)) {

		// Are we in taxonomy context?
		if (is_tax()) {
			$term = get_query_var('term');
			$taxonomy = get_query_var('taxonomy');
		}

		// Require taxonomy if $term is not object
		if (empty($taxonomy) || ! taxonomy_exists($taxonomy)) {
			return null;
		}

		if (is_int($term)) {
			$term = get_term($term, $taxonomy);
			
		} else if (is_string($term)) {
			
			$orig = $term;
			$term = get_term_by('slug', $term, $taxonomy);
			
			if (null === $term) {
				// slug didn't work, try name
				$term = get_term_by('name', $orig, $taxonomy);
			}
		}
	}

	return $term ? $term->parent === 0 : null;
}

/**
 * Outputs unordered list of terms with nested children 1 level deep.
 */
function taxonomy_terms_ul($taxonomy, $parent_id = null, $args = array()) {

	if (! taxonomy_exists($taxonomy)) {
		return;
	}
	
	// if a term id was passed, make sure it exists
	if (null !== $parent_id && 0 !== $parent_id && ! term_exists($parent_id, $taxonomy)) {
		return;
	}

	$args = wp_parse_args($args, array(
		'orderby' => 'slug',
		'hide_empty' => true,
	));

	if (is_numeric($parent_id)) {// pass 0 to get only top-level terms
		$args['parent'] = (int)$parent_id;
	}
	
	$_cachename = 'hierarchy/'.$parent_id.'/'.$taxonomy;

	if ($cached = wp_cache_get($_cachename, 'terms')) {
		return $cached;
	}

	$terms = get_terms($taxonomy, $args);

	// Filter parent terms
	$pterms = wp_list_filter($terms, array('parent' => 0));

	$html = '<ul class="'.$taxonomy.'-terms angles">';

	foreach ($pterms as $pterm) :

		$html .= '<li>'.term_link_html($pterm).'</li>';

		// Filter children of parent
		$children = wp_list_filter($terms, array('parent' => $pterm->term_id));

		if ($children) {

			$html .= '<ul class="children double-angles">';

			foreach ($children as $child) {
				$html .= '<li>'.term_link_html($child).'</li>';
			}

			$html .= '</ul>';
		}
	endforeach;

	$html .= '</ul>';

	// Cache for 12 hours
	wp_cache_set($_cachename, $html, 'terms', DAY_IN_SECONDS/2);
	
	return $html;
}

/**
 * Returns link HTML for a term.
 */
function term_link_html($term, $text = 'name', $attributes = array('title' => 'description')) {

	$html = '<a ';

	if (! empty($attributes)) {
		foreach ($attributes as $attr => $val) {
			if (isset($term->$val) && ! empty($term->$val)) {
				$html .= $attr.'="'.$term->$val.'" ';
			} else {
				$html .= $attr.'="'.$val.'" ';
			}
		}
	}

	$html .= 'href="'.esc_url(get_term_link($term)).'">';

	if (! isset($term->$text)) {
		$html .= $text;
	} else if (! empty($term->$text)) {
		$html .= $term->$text;
	} else {
		$html .= $term->name;
	}

	$html .= '</a>';

	return $html;
}

/**
 * Returns the number of posts (or custom post-types) with the given taxonomy term.
 * 
 * @param object|string|int $term The term object, ID, or slug.
 * @param string $taxonomy The term taxonomy.
 * @param string $post_type [Optional] The post type to count. Default "post".
 * @return int Number of posts with the given term.
 */
function get_post_count_by_term($term, $taxonomy, $post_type = 'post', $cache = true) {

	if (! taxonomy_exists($taxonomy) || ! term_exists($term, $taxonomy)) {
		return null;
	}

	if (! is_object($term)) {
		if (is_numeric($term) && is_taxonomy_hierarchical($taxonomy)) {
			$term = get_term($term, $taxonomy);
		} else {
			$term = get_term_by('slug', $term, $taxonomy);
		}
	}

	if (! $term || is_wp_error($term)) {
		return null;
	}
	
	// Set the transient using ID's so the name does not exceed 45 characters
	$term_id = $term->term_id;
	$taxonomy_id = $term->term_taxonomy_id;
	
	if ($cache) {
		$transient = "{$post_type}-{$taxonomy_id}-{$term_id}-count";
		$count = get_transient($transient);
	}
	
	if (! $cache || false === $count) {
		// if transient is expired, do the query
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'numberposts' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => $taxonomy,
					'terms' => $term_id,
				),
			),
		);
		$count = count(get_posts($args));
		if ($cache) {
			// Cache for 4 hours
			set_transient($transient, $count, DAY_IN_SECONDS/6);
		}
	}

	return $count;
}
