<?php

use WordPress\App;

/**
 * Loads a template file within the current post scope.
 * 
 * Allows for use of '$this' within templates, which refers to
 * the current \WordPress\Post\Post.
 * 
 * @param string $template Template file.
 * @return void
 */
function include_template($template) {
	if ($post = App::instance()->getPost()) {
		$post->includeFile($template);
	} else {
		load_template($template);
	}
}

/**
 * Loads the main template.
 */
function main_template() {
	include_template(App::instance()->get('theme')->getMainTemplate());
}

/**
 * Loads the sidebar template.
 */
function sidebar_template() {
	$theme = App::instance()->get('theme');
	include_template($theme->wrapTemplate($theme->getTemplatePartPath('sidebar').'.php'));
}

/**
 * Whether to display the sidebar.
 * @return boolean
 */
#function display_sidebar() {
#	return App::instance()->get('theme')->displaySidebar();
#}

/**
 * Locates and loads a theme template.
 * 
 * Replacement for WordPress function get_template_part()
 * 
 * @see get_template_part()
 * 
 * @param string $slug Template slug.
 * @param string $name [Optional] Optional template name.
 * @return void
 */
function template_part($slug, $name = null) {
	
	$slug = App::instance()->get('theme')->getTemplatePartPath($slug);
	
	do_action("get_template_part_{$slug}", $slug, $name);

	$templates = array();
	if (! empty($name)) {
		$templates[] = "{$slug}-{$name}.php";
	}
	$templates[] = "{$slug}.php";

	include_template(locate_template($templates));
}

/**
 * Returns a breadcrumbs object.
 * 
 * @param boolean $back_link [Optional] Whether to display a back link. Default true.
 * @return WordPress\Theme\Breadcrumbs Breadcrumbs object.
 */
function get_breadcrumbs($back_link = false) {
	$class = apply_filters('breadcrumbs_class', 'WordPress\Theme\Breadcrumbs');
	return new $class($back_link);
}

/**
 * Returns the current page title.
 * @return string
 */
function get_page_title() {
	if (is_home()) {
		if ($post_page = get_option('page_for_posts', true)) {
			return get_the_title($post_page);
		} else {
			return __('Latest Posts', 'roots');
		}
	} else if (is_archive()) {
		return get_archive_title();
	} else if (is_search()) {
		return sprintf(__('Search Results for %s', 'roots'), get_search_query());
	} else if (is_404()) {
		return __('Not Found', 'roots');
	}
	return get_the_title();
}

/**
 * Returns the page title for a post archive.
 * 
 * @return string Page title
 */
function get_archive_title() {
	if (is_category() || is_tag()) {
		return single_term_title('', false);
	} else if (is_tax()) {
		return single_tax_title('', false);
	} else if (is_post_type_archive()) {
		return post_type_archive_title('', false);
	} else if (is_day()) {
		return sprintf('Day: %s', '<span>'.get_the_date().'</span>');
	} else if (is_month()) {
		return sprintf('Month: %s', '<span>'.get_the_date(_x('F Y', 'monthly archives date format')).'</span>');
	} else if (is_year()) {
		return sprintf('Year: %s', '<span>'.get_the_date(_x('Y', 'yearly archives date format')).'</span>');
	} else if (is_author()) {
		return sprintf('Author: %s', '<span class="vcard">'.get_the_author().'</span>');
	} else {
		return 'Archives';
	}
}

/**
 * Returns or displays the page title for a taxonomy term archive.
 * 
 * @param string $before [Optional] String to prepend to title.
 * @param boolean $display [Optional] Whether to display the title. Default true.
 * @return string|null Title string if $display = false, otherwise null.
 */
function single_tax_title($before = '', $display = true) {
	
	$tax = get_query_var('taxonomy');
	$term = get_query_var('term');
	
	if ('post_format' === $tax) {
		
		switch($format = substr($term, 12)) {
			case 'aside':
			case 'image':
			case 'video':
			case 'link':
			case 'audio':
			case 'chat':
				$title = ucfirst($format).'s';
				break;
			case 'gallery':
				$title = 'Galleries';
				break;
			case 'status':
				$title = 'Statuses';
				break;
			default:
				$title = 'Archives';
				break;	
		}
		
		$title = apply_filters('post_format_archive_title', $title, $format);
		
	} else {
		$taxonomy = get_taxonomy($tax);
		$title = '<span class="taxonomy">'.$taxonomy->labels->singular_name
			.':</span> <span class="term">'.single_term_title('', false).'</span>';
	}
	
	$title = apply_filters('single_tax_title', $title, $tax, $term);
	
	if ($display) {
		echo $before.$title;
	} else {
		return $before.$title;
	}
}

/**
 * ====================================
 * 		Script & Style functions
 * ====================================
 */

/**
 * Registers JS from Google, replacing the existing handle if it exists.
 * 
 * @param string $handle The script handle.
 * @param array|string $args The parameters for wp_register_script(), minus 'handle'.
 * @return boolean True if registered successfully, otherwise false.
 */
function register_google_script($handle, $args = array()) {

	$args = wp_parse_args($args, array(
		'url' => '',
		'deps' => array(),
		'ver' => '',
		'in_footer' => false,
	));
	
	if ('jquery' === $handle) {
		if (empty($args['ver'])) {
			$args['ver'] = '2.1.4';
		}
		$args['url'] = '//ajax.googleapis.com/ajax/libs/jquery/'.$args['ver'].'/jquery.min.js';
		$args['ver'] = false;
	}

	$args = apply_filters('register_google_script', $args, $handle);
			
	if (empty($args['url'])) {
		return false;
	}
	
	wp_deregister_script($handle);
	wp_register_script($handle, $args['url'], $args['deps'], $args['ver'], $args['in_footer']);
	
	return true;
}

function register_google_font($font_family, $font_styles = array(), $deps = array(), $version = false) {

	if (empty($font_styles)) {
		// default style is 400 (normal)
		$font_styles = array('400');
	}

	$handle = 'google-font-'.strtolower(str_replace(array('.',' '), '-', $font_family));

	$url = 'http://fonts.googleapis.com/css?family='.urlencode($font_family).':'.implode(',', array_map('urlencode', $font_styles));

	wp_register_style($handle, $url, $deps, $version);

	return $handle;
}

function enqueue_google_font($font_family, $font_styles = array(), $deps = array(), $version = false) {

	if (! empty($font_styles) || ! empty($deps) || $version) {
		$handle = register_google_font($font_family, $font_styles, $deps, $version);	
	} else {
		$handle = 'google-font-'.strtolower(str_replace(array('.',' '), '-', $font_family));
	}

	wp_enqueue_style($handle);
}
