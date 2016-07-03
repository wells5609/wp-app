<?php

namespace WordPress\Cli;

use WordPress\DependencyInjection\Injectable;
use ReflectionMethod;

class Console extends Injectable
{
	
	/**
	 * Current request instance.
	 * 
	 * @var \WordPress\Cli\Request
	 */
	protected $request;
	
	/**
	 * StdIo instance.
	 * 
	 * @var \WordPress\Cli\StdIo
	 */
	protected $io;
	
	/**
	 * Task handler instances.
	 * 
	 * @var \WordPress\Cli\TaskHandlerInterface[]
	 */
	protected $handlers = [];
	
	/**
	 * String appended to task handler classes.
	 * 
	 * @var string
	 */
	protected $handlerClassSuffix = 'TaskHandler';
	
	/**
	 * String appended to action methods.
	 * 
	 * @var string
	 */
	protected $actionMethodSuffix = 'Action';
	
	/**
	 * Handler method to call when no action is found.
	 * 
	 * @var string
	 */
	protected $defaultActionMethod = '__invoke';
	
	/**
	 * Constructor.
	 * 
	 * @param \WordPress\Cli\Request
	 * @param \WordPress\Cli\StdIo [Optional]
	 */
	public function __construct(Request $request, StdIo $io = null) {
		$this->request = $request;
		$this->io = $io ?: new StdIo;
	}
	
	/**
	 * Returns the Request instance.
	 * 
	 * @return \WordPress\Cli\Request
	 */
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * Returns the StdIo instance.
	 * 
	 * @return \WordPress\Cli\StdIo
	 */
	public function getIo() {
		return $this->io;
	}
	
	/**
	 * Add a task handler.
	 * 
	 * @param \WordPress\Cli\TaskHandlerInterface $handler
	 * @return void
	 */
	public function addHandler(TaskHandlerInterface $handler) {
		$this->handlers[$handler->getTaskName()] = $handler;
	}
	
	/**
	 * Dispatches the request to a task handler.
	 * 
	 * @throws \WordPress\Cli\Task\NotHandledException
	 * @throws \WordPress\Cli\Exception
	 * 
	 * @return mixed
	 */
	public function handle() {
	
		$task = $this->request->getTask();
		$handler = $this->resolveHandler($task);
		$action = $this->request->getAction();
		$method = empty($action) ? $this->defaultActionMethod : $action.$this->actionMethodSuffix;
		
		if (! method_exists($handler, $method)) {
			throw new Task\NotHandledException("'{$method}' method not found on handler.");
		}
		
		$handler->setRequest($this->request);
		$handler->setIo($this->io);
		
		return $this->invokeHandler($handler, $method);
	}
	
	/**
	 * Resolves a task to a TaskHandlerInterface
	 * 
	 * @param string $task
	 * 
	 * @throws \WordPress\Cli\Task\NotHandledException if a task handler cannot be found
	 * 
	 * @return \WordPress\Cli\TaskHandlerInterface
	 */
	protected function resolveHandler($task) {
		
		if (isset($this->handlers[$task])) {
			return $this->handlers[$task];
		}
		
		$di = $this->getDI();
		
		$handlerName = $task.$this->handlerClassSuffix;
		
		if (! $di->has($handlerName)) {
			throw new Task\NotHandledException("No handler found for cli task '$task'.");
		}
		
		return $di->get($handlerName);
	}
	
	/**
	 * Invokes a task handler method using the request arguments.
	 * 
	 * @param \WordPress\Cli\TaskHandlerInterface $handler
	 * @param string $method
	 * 
	 * @throws \WordPress\Cli\Exception if method cannot be called
	 * 
	 * @return mixed
	 */
	protected function invokeHandler(TaskHandlerInterface $handler, $method) {
		
		$params = array();
		$reflection = new ReflectionMethod($handler, $method);
		
		foreach($reflection->getParameters() as $i => $param) {
			
			$name = $param->getName();
			
			if ($this->request->hasArg($name)) {
				$params[$name] = $this->request->getArg($name);
			} else if ($this->request->hasArg($i)) {
				$params[$name] = $this->request->getArg($i);
			} else if ($param->isDefaultValueAvailable()) {
				$params[$name] = $param->getDefaultValue();
			} else {
				throw new Exception("Missing parameter '$name'.");
			}
		}
		
		return $reflection->invokeArgs($handler, $params);
	}
	
}
