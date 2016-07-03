<?php

namespace WordPress\Events\Ajax;

use Closure;
use RuntimeException;

class Action
{
	
	/**
	 * Name of the action, without 'wp_ajax_' prefix.
	 * 
	 * @var string
	 */
	protected $action;
	
	/**
	 * The callable invoked when the action is fired.
	 * 
	 * @var callable
	 */
	protected $callback;
	
	/**
	 * The callable invoked when the action is fired and 
	 * the user does not have the required permissions.
	 * 
	 * @var callable
	 */
	protected $noPrivCallback;
	
	/**
	 * The action callback priority.
	 * 
	 * Default = 10
	 * 
	 * @var int
	 */
	protected $priority = 10;
	
	/**
	 * The number of callback arguments.
	 * 
	 * Default = 1
	 * 
	 * @var int
	 */
	protected $numberArguments = 1;
	
	/**
	 * Constructor.
	 * 
	 * @param string $action
	 * @param callable $callback [Optional]
	 */
	public function __construct($action, $callback = null, $priority = 10, $num_args = 1) {
		$this->action = $action;
		if ($callback) {
			$this->setCallback($callback);
		}
		$this->priority = $priority;
		$this->numberArguments = $num_args;
	}
	
	public function setPriority($priority) {
		$this->priority = $priority;
	}
	
	public function setCallback(callable $callback) {
		$this->callback = $callback;
	}
	
	public function setNoPrivCallback(callable $callback) {
		$this->noPrivCallback = $callback;
	}
	
	public function setNumberArguments($num) {
		$this->numberArguments = $num;
	}
	
	public function init() {
		
		if (empty($this->callback)) {
			throw new RuntimeException("Missing action callback for '$this->action'");
		}
		
		add_action(
			'wp_ajax_nopriv_'.$this->action, 
			$this->noPrivCallback ?: $this->callback, 
			$this->priority, 
			$this->numberArguments
		);
		
		add_action(
			'wp_ajax_'.$this->action, 
			$this->callback, 
			$this->priority, 
			$this->numberArguments
		);
	}
	
}
