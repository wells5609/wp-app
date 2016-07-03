<?php

/**
 * Fetches a related custom post by the given field name.
 * 
 * @param int|\WordPress\Model\Post $post
 * @param string $field_name
 * @return \WordPress\Model\Post
 */
function get_related_custom_post($post, $field_name) {
	if (is_object($post)) {
		$post = $post->ID;
	}
	if (! $value = get_field($field_name, $post)) {
		return null;
	}
	if (is_array($value)) {
		$value = reset($value);
	}
	return custom_post($value);
}

/**
 * Fetches multiple related custom posts by the given field name.
 * 
 * @param int|\WordPress\Model\Post $post
 * @param string $field_name
 * @return array
 */
function get_related_custom_posts($post, $field_name) {
	if (is_object($post)) {
		$post = $post->ID;
	}
	$value = get_field($field_name, $post);
	if (empty($value) && ! is_array($value)) {
		return $value;
	}
	return array_map('custom_post', $value);
}
