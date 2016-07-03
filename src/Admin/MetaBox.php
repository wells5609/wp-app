<?php

namespace WordPress\Admin;

class MetaBox
{

	const DASH = 'dashboard';
	const POSTS = 'edit-post';
	const EDIT_POST = 'post';
	const PAGES = 'edit-page';
	const EDIT_PAGE = 'page';
	const PROFILE = 'profile';

	protected $id;
	protected $title;
	protected $callback;
	protected $screen;
	protected $context = 'advanced';
	protected $priority = 'default';
	protected $callbackArgs;

	public function __construct($id, $title, callable $callback = null, $screen = null) {
		$this->id = $id;
		$this->title = $title;
		$this->callback = $callback ?: array($this, '__invoke');
		$this->screen = $screen;
		add_action('add_meta_boxes', array($this, 'register'));
	}
	
	public function setScreen($screen) {
		$this->screen = $screen;
	}

	public function setContext($context) {
		$this->context = $context;
	}

	public function setPriority($priority) {
		$this->priority = $priority;
	}

	public function setCallbackArgs(array $args) {
		$this->callbackArgs = $args;
	}

	public function register() {
		add_meta_box($this->id, $this->title, $this->callback, $this->screen, $this->context, $this->priority, $this->callbackArgs);
	}

	public function __invoke() {
		var_dump(func_get_args());
	}
}
