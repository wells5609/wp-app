<?php

namespace WordPress\Model\Post;

use WP_Post;
use WordPress\App;
use WordPress\Model\AbstractModel;
use RuntimeException;

class Post extends AbstractModel
{
	
	/**
	 * Post ID.
	 * @var int
	 */
	public $ID;

	/**
	 * ID of post author.
	 * A numeric string, for compatibility reasons.
	 * @var string
	 */
	public $post_author = 0;

	/**
	 * The post's local publication time.
	 * @var string
	 */
	public $post_date = '0000-00-00 00:00:00';

	/**
	 * The post's GMT publication time.
	 * @var string
	 */
	public $post_date_gmt = '0000-00-00 00:00:00';

	/**
	 * The post's content.
	 * @var string
	 */
	public $post_content = '';

	/**
	 * The post's title.
	 * @var string
	 */
	public $post_title = '';

	/**
	 * The post's excerpt.
	 * @var string
	 */
	public $post_excerpt = '';

	/**
	 * The post's status.
	 * @var string
	 */
	public $post_status = 'publish';

	/**
	 * Whether comments are allowed.
	 * @var string
	 */
	public $comment_status = 'open';

	/**
	 * Whether pings are allowed.
	 * @var string
	 */
	public $ping_status = 'open';

	/**
	 * The post's password in plain text.
	 * @var string
	 */
	public $post_password = '';

	/**
	 * The post's slug.
	 * @var string
	 */
	public $post_name = '';

	/**
	 * URLs queued to be pinged.
	 * @var string
	 */
	public $to_ping = '';

	/**
	 * URLs that have been pinged.
	 * @var string
	 */
	public $pinged = '';

	/**
	 * The post's local modified time.
	 * @var string
	 */
	public $post_modified = '0000-00-00 00:00:00';

	/**
	 * The post's GMT modified time.
	 * @var string
	 */
	public $post_modified_gmt = '0000-00-00 00:00:00';
	
	/**
	 * The post's filtered content.
	 * @var string
	 */
	public $post_content_filtered = '';
	
	/**
	 * ID of a post's parent post.
	 * @var int
	 */
	public $post_parent = 0;

	/**
	 * The unique identifier for a post, not necessarily a URL, used as the feed GUID.
	 * @var string
	 */
	public $guid = '';

	/**
	 * A field used for ordering posts.
	 * @var int
	 */
	public $menu_order = 0;

	/**
	 * The post's type, like post or page.
	 * @var string
	 */
	public $post_type = 'post';

	/**
	 * An attachment's mime type.
	 * @var string
	 */
	public $post_mime_type = '';

	/**
	 * Cached comment count.
	 * A numeric string, for compatibility reasons.
	 * @var string
	 */
	public $comment_count = 0;
	
	/**
	 * Post permalink URI.
	 * 
	 * @var string
	 */
	protected $uri;
	
	/**
	 * Post metadata.
	 * 
	 * @var array
	 */
	protected $meta;
	
	/**
	 * The current post.
	 * 
	 * @var \WordPress\Model\Post
	 */
	protected static $current;
	
	/**
	 * Post type class map.
	 * 
	 * @var array
	 */
	protected static $classes = [
		'_default' => 'WordPress\Model\Post\Post'
	];
	
	/**
	 * Returns an instance of the given post or the current post.
	 * 
	 * @param mixed $post [Optional]
	 * @return \WordPress\Model\Post\Post
	 */
	public static function instance($post = null) {
		if (isset($post)) {
			return static::forgeObject($post);
		}
		if (! isset(static::$current) || static::$current->ID !== $GLOBALS['post']->ID) {
			static::$current = static::forgeObject($GLOBALS['post']);
		}
		return static::$current;
	}
	
	/**
	 * Returns the class to use for a given post type.
	 * 
	 * @param string $type
	 * @return string
	 */
	public static function getPostTypeClass($type) {
		if (is_object($type)) {
			$type = $type->post_type;
		}
		if (isset(static::$classes[$type])) {
			return static::$classes[$type];
		}
		return static::$classes['_default'];
	}
	
	/**
	 * Sets the class to use for a given post type.
	 * 
	 * @param string $type
	 * @param string $class
	 * @return void
	 */
	public static function setPostTypeClass($type, $class) {
		if (is_object($type)) {
			$type = $type->post_type;
		}
		static::$classes[$type] = $class;
	}
	
	/**
	 * Overwrite forgeObject() implementation.
	 * 
	 * @param mixed $post
	 * @return \WordPress\Model\Post\Post
	 */
	public static function forgeObject($post) {
		if (empty($post)) {
			return null;
		}
		if (! $post instanceof WP_Post) {
			if (! $post = get_post($post)) {
				return null;
			}
		}
		$class = static::getPostTypeClass($post->post_type);
		return new $class($post);
	}
	
	/**
 	 * Overwrite find() implementation to use get_posts().
	 *
	 * @param array $where Arguments for get_posts()
	 * @return mixed
	*/	
	public static function find(array $where) {
		$class = get_called_class();
		$posts = get_posts($where);
		if (empty($posts) || ! is_array($posts)) {
			return $results;
		}
		return array_map($class.'::forgeObject', $results);
	}
	
	/**
 	 * Overwrite findOne() implementation to use get_posts().
	 *
	 * @param array $where Arguments for get_posts()
	 * @return mixed
	*/	
	public static function findOne(array $where) {
		$class = get_called_class();
		$posts = get_posts($where);
		if (empty($posts) || ! is_array($posts)) {
			return $posts;
		}
		return $class::forgeObject(reset($posts));
	}
	
	/**
	 * Overwrite import() implementation.
	 * 
	 * @param mixed $data
	 * @return void
	 */
	public function import($data) {
		if ($data instanceof WP_Post) {
			$data = $data->to_array();
		} else if (! is_array($data)) {
			$data = is_object($data) ? get_object_vars($data) : (array)$data;
		}
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * Returns the value of the primary key (ID).
	 * 
	 * @return int
	 */
	public function getPrimaryKeyValue() {
		return $this->ID;
	}
	
	/**
	 * Saves the post.
	 */
	public function save(array $data = null) {
		if (isset($this->ID)) {
			$result = $this->update($data);
		} else {
			$result = $this->insert($data);
		}
		return (bool)$result;
	}
	
	/**
	 * Insert the post.
	 * 
	 * @uses wp_insert_post()
	 * 
	 * @param array $data [Optional]
	 * @return int ID of the newly inserted post, or 0 if failed.
	 */
	public function insert(array $data = null) {
		if (isset($this->ID)) {
			throw new RuntimeException("Cannot insert: post already exists.");
		}
		$this->beforeInsert();
		if (isset($data)) {
			$this->import($data);
		}
		$result = wp_insert_post($this->toArray());
		if ($result != 0) {
			$this->ID = (int)$result;
		}
		$this->afterInsert($result);
		return $result;
	}
	
	/**
	 * Update the post.
	 * 
	 * @uses wp_update_post()
	 * 
	 * @param array $data [Optional]
	 * @return boolean
	 */
	public function update(array $data = null) {
		$this->beforeUpdate();
		if (isset($data)) {
			$this->import($data);
		}
		$result = (bool) wp_update_post($this->toArray());
		$this->afterUpdate($result);
		return $result;
	}
	
	/**
	 * Delete the post.
	 * 
	 * @uses wp_delete_post()
	 * 
	 * @return boolean
	 */
	public function delete() {
		$this->beforeDelete();
		if (! isset($this->ID)) {
			return null;
		}
		$result = (bool) wp_delete_post($this->ID);
		$this->afterDelete($result);
		return $result;
	}
	
	/**
	 * Returns the post title.
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->post_title;
	}
	
	/**
	 * Returns the post permalink URI.
	 * 
	 * @return string
	 */
	public function getUri() {
		if (! isset($this->uri) && isset($this->ID)) {
			$this->uri = get_permalink($this->ID);
		}
		return $this->uri;
	}
	
	/**
	 * Returns the post type.
	 * 
	 * @return string
	 */
	public function getPostType() {
		return $this->post_type;
	}
	
	/**
	 * Returns the post type object.
	 * 
	 * @return \WordPress\Model\Post\Type
	 */
	public function getPostTypeObject() {
		return Post\Type::instance($this->post_type);
	}
	
	/**
	 * Checks whether the post is a custom type.
	 * 
	 * @return boolean
	 */
	public function isCustomType() {
		return $this->getPostTypeObject()->isCustom();
	}
	
	/**
	 * Returns post meta.
	 * 
	 * @param string $key [Optional]
	 * @param boolean $single [Optional] Default = false
	 * @return mixed
	 */
	public function getMeta($key = null, $single = false) {
	
		if (! isset($this->meta)) {
			$this->meta = $this->getRelatedRecords('postmeta');
		}
		
		if (null === $key) {
			return $this->meta;
		} else if (isset($this->meta[$key])) {
			$value = $this->meta[$key]->meta_value;
			if ($single && is_array($value)) {
				return reset($value);
			}
			return $value;
		}
		
		return null;
	}
	
	/**
	 * Loads a file within the object's scope (i.e. with $this available in the file).
	 * 
	 * @param string $__file
	 * @param boolean $__return [Optional] Default = false. Whether to return the output rather than print it.
	 * @return void|string
	 */
	public function includeFile($__file, $__return = false) {
		
		if ($__return) {
			ob_start();
		}
		
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
		
		/** @var $app \WordPress\App */
		$app = App::instance();
		
		/** @var $di \WordPress\DI */
		$di = $app;
		
		/** @var $theme \WordPress\Theme\ActiveTheme */
		$theme = $app->get('theme');
		
		if (! empty($wp_query->query_vars) && is_array($wp_query->query_vars)) {
			extract($wp_query->query_vars, EXTR_SKIP);
		}
		
		if ($di->has('templateVars')) {
			extract($di->get('templateVars'), EXTR_SKIP);
		}
		
		include $__file;
		
		if ($__return) {
			return ob_get_clean();
		}
	}
	
}
