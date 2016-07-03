<?php

namespace WordPress\Theme;

/**
 * Determines whether or not to display the sidebar based on an array of conditional tags or page
 * templates.
 *
 * If any of the is_* conditional tags or is_page_template(template_file) checks return true, the
 * sidebar will NOT be displayed.
 *
 * @link http://roots.io/the-roots-sidebar/
 */
class Sidebar
{
	
	protected static $instance;
	protected $display;
	protected $conditionals;
	protected $templates;
	protected $pages;
	
	/**
	 * Returns the default Sidebar instance.
	 * 
	 * @return Sidebar
	 */
	public static function instance() {
		if (! isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	/**
	 * Constructor.
	 * 
	 * @param array $conditionals
	 * @param array $templates
	 * @param array $pages
	 */
	public function __construct(array $conditionals = [], array $templates = [], array $pages = []) {
		static::$instance = $this;
		$this->conditionals = $conditionals;
		$this->templates = $templates;
		$this->pages = $pages;
	}
	
	/**
	 * Magic __get access to protected properties.
	 * 
	 * @param string $key
	 * 
	 * @throws \OutOfBoundsException
	 * 
	 * @return mixed
	 */
	public function __get($key) {
		if (! property_exists($this, $key)) {
			throw new \OutOfBoundsException("Unknown property '$key'.");
		}
		return $this->$key;
	}

	/**
	 * Conditional tag checks.
	 * 
	 * Any of these conditional tags that return true won't show the sidebar.
	 *
	 * @link http://codex.wordpress.org/Conditional_Tags
	 * 
	 * To use a function that accepts arguments, use the following format:
	 * 		array('function_name', array('arg1', 'arg2'))
	 *
	 * The second element must be an array even if there's only 1 argument.
	 * 
	 * @param string $function
	 * @param null|array $args [Optional]
	 * 
	 * @return \SidebarWrapper
	 */
	public function hideIf($function, $args = null) {
		$condition = isset($args) ? array($function, (array)$args) : $function;
		$this->conditionals[] = $condition;
		return $this;
	}

	/**
	 * Page template checks (via is_page_template())
	 * 
	 * Any of these page templates that return true won't show the sidebar.
	 * 
	 * @param string $template
	 * 
	 * @return \SidebarWrapper
	 */
	public function hideIfTemplate($template) {
		$this->templates[] = $template;
		return $this;
	}

	/**
	 * Page name checks (checked via $post->post_name and $post->post_title).
	 * 
	 * Any of these page names that return true won't show the sidebar.
	 * 
	 * @param string $page
	 * 
	 * @return \SidebarWrapper
	 */
	public function hideIfPage($page) {
		$this->pages[] = $page;
		return $this;
	}

	/**
	 * Checks and sets whether to display the sidebar.
	 */
	public function display() {
		if (! isset($this->display)) {
			if ($this->checkPageNames() || $this->checkConditionals() || $this->checkTemplates()) {
				$this->display = false;
			} else {
				$this->display = true;
			}
			$this->display = apply_filters('theme/display_sidebar', $this->display);
		}
		return $this->display;
	}
	
	/**
	 * Checks whether to display the sidebar.
	 * 
	 * @return boolean
	 */
	public static function show() {
		return static::$instance->display();
	}

	/**
	 * Loads the sidebar template.
	 */
	public static function render($template = 'parts/sidebar', $name = null) {
		return get_template_part($template);
	}

	/**
	 * Returns the sidebar contents as a string.
	 * 
	 * @param string $template [Optional] Default = 'parts/sidebar'
	 * @param string $name [Optional]
	 * 
	 * @return string
	 */
	public static function getContents($template = 'parts/sidebar', $name = null) {
		ob_start();
		static::render($template, $name);
		return ob_get_clean();
	}
	
	protected function checkConditionals() {

		if (! empty($this->conditionals)) {
			foreach($this->conditionals as $tag) {
				if ($this->checkConditionalTag($tag)) {
					return true;
				}
			}
		}

		return false;
	}

	protected function checkTemplates() {

		if (! empty($this->templates)) {
			foreach($this->templates as $template) {
				if (is_page_template($template)) {
					return true;
				}
			}
		}

		return false;
	}

	protected function checkPageNames() {

		if (! empty($this->pages)) {
			$post = get_post();
			if ('page' === $post->post_type) {
				foreach($this->pages as $page) {
					if ($page == $post->post_name || $page == $post->post_title) {
						return true;
					}
				}
			}
		}

		return false;
	}

	protected function checkConditionalTag($tag) {

		if (is_array($tag)) {
			list($tag, $arg) = $tag;
		} else {
			$arg = false;
		}

		if (function_exists($tag)) {
			return $arg ? $tag($arg) : $tag();
		}

		return false;
	}

}
