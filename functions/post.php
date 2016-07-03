<?php

/**
 * Transforms a WP_Post instance into a \WordPress\Model\Post.
 * 
 * @param \WP_Post $post
 * @return \WordPress\Model\Post
 */
function custom_post(WP_Post $post) {
	return WordPress\App::instance()->get('modelManager')->getInstance('post', $post);
}

/**
 * Returns a \WordPress\Model\Post instance for the given post.
 * 
 * @param null|int|WP_Post $post [Optional] Post ID or object, or null to use global.
 * @return \WordPress\Model\Post
 */
function get_custom_post($post = null) {
	
	if (null === $post) {
		return WordPress\App::instance()->getPost();
	} else if (! $post instanceof WP_Post) {
		$post = get_post($post);
	}
	
	return WordPress\App::instance()->get('modelManager')->getInstance('post', $post);
}

/**
 * Returns an array of custom posts matching the given arguments.
 * 
 * @param array $args
 * @return array
 */
function get_custom_posts(array $args) {
	$posts = get_posts($args);
	return empty($posts) ? null : array_map('custom_post', $posts);
}

/**
 * Registers the custom post class for a given post type.
 * 
 * @param string $post_type
 * @param string $class
 * @return void
 */
function register_post_type_class($post_type, $class) {
	WordPress\Model\Post\Post::setPostTypeClass($post_type, $class);
}

/**
 * Registers the custom post classes for an array of post-types.
 * 
 * @param array $classes
 * @return void
 */
function register_post_type_classes(array $classes) {
	foreach ($classes as $post_type => $class) {
		register_post_type_class($post_type, $class);
	}
}

function get_post_parent($post) {
	if (! $post instanceof WP_Post) {
		$post = get_post($post);
	}
	if (! $post || empty($post->ID) || empty($post->post_parent) || $post->parent == $post->ID) {
		return null;
	}
	return get_post($post->post_parent);
}

/**
 * Retrieve parents of a post.
 *
 * @param null|int|WP_Post $post [Optional] Post ID or post object, or null to use the global post.
 * 
 * @return array Parent post objects or an empty array if none are found.
 */
function get_post_parents($post = null) {
	
	if (! $post || ! $post instanceof WP_Post) {
		$post = get_post($post);
	}
	
	$parents = array();
	
	if (! $post || empty($post->ID) || empty($post->post_parent) || $post->post_parent == $post->ID) {
		return $parents;
	}
	
	$parent = get_post($post->post_parent);
	
	while ($parent) {
		$parents[] = $parent;
		$parent = get_post_parent($parent);
	}
	
	return $parents;
}

/**
 * Returns true if post-type is custom.
 *
 * @param mixed $post The WP_Post object, ID, or post-type string, or null to use global.
 * @return boolean True if custom post-type, false if built-in.
 */
function is_post_type_custom($post = null) {

	if (empty($post)) {
		$type = get_post_type();
	} else if (is_numeric($post)) {
		$type = get_post($post)->post_type;
	} else if (is_string($post)) {
		$type = $post;
	} else if (is_object($post)) {
		$type = isset($post->post_type) ? $post->post_type : $post->name;
	} else {
		return null;
	}

	return ! in_array($type, array('post', 'page', 'attachment', 'revision', 'nav_menu_item'));
}

/**
 * Returns single (default) or plural label for a post-type.
 * 
 * @param object|int $post The post to retrieve a label for, or the post type name.
 * @param boolean $plural Whether to return the plural label. Default false.
 * @return string The post type's label. 
 */
function get_post_type_label($post = null, $plural = false) {
	
	if (empty($post)) {
		$type = get_post_type();
	} else if (is_object($post)) {
		$type = isset($post->post_type) ? $post->post_type : $post->name;
	} else if (is_numeric($post)) {
		$type = get_post($post)->post_type;
	} else if (is_string($post)) {
		$type = $post;
	}
	
	if ($object = get_post_type_object($type)) {
		return $plural ? $object->labels->name : $object->labels->singular_name;
	}
}

/**
 * Returns true if post status is custom.
 * 
 * @param string $status Post status string.
 * @return boolean True if given status is custom, otherwise false.
 */
function is_post_status_custom($status) {
	return ! in_array($status, get_post_statuses(), true);
}
