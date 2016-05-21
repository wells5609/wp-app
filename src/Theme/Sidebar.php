<?php
/**
 * Determines whether or not to display the sidebar based on an array of conditional tags or page
 * templates.
 *
 * If any of the is_* conditional tags or is_page_template(template_file) checks return true, the
 * sidebar will NOT be displayed.
 *
 * @link http://roots.io/the-roots-sidebar/
 */
namespace WordPress\Theme;

class Sidebar
{
	public $display = true;
	
	protected $conditionals;
	protected $templates;
	protected $pages;
	
	public function __construct(array $conditionals = array(), array $templates = array(), array $pages = array()) {
		$this->conditionals = $conditionals;
		$this->templates = $templates;
		$this->pages = $pages;
	}
	
	/**
	 * Conditional tag checks (http://codex.wordpress.org/Conditional_Tags)
	 * Any of these conditional tags that return true won't show the sidebar
	 *
	 * To use a function that accepts arguments, use the following format:
	 *
	 * array('function_name', array('arg1', 'arg2'))
	 *
	 * The second element must be an array even if there's only 1 argument.
	 */
	public function hideIf($function, $args = null) {
		$condition = isset($args) ? array($function, (array)$args) : $function;
		$this->conditionals[] = $condition;
		return $this;	
	}
	
	/**
	 * Page template checks (via is_page_template())
	 * Any of these page templates that return true won't show the sidebar
	 */
	public function hideIfTemplate($template) {
		$this->templates[] = $template;
		return $this;
	}
	
	/**
	 * Page names (checked via $post->post_name and $post->post_title)
	 * Any of these page names that return true won't show the sidebar
	 */
	public function hideIfPage($page) {
		$this->pages[] = $page;
		return $this;
	}
	
	public function check() {
		if ($this->checkPageNames() || $this->checkConditionals() || $this->checkTemplates()) {
			$this->display = false;
		} else {
			$this->display = true;
		}
		$this->display = apply_filters('theme/display_sidebar', $this->display);
		return $this->display;
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
