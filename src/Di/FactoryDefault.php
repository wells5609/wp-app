<?php

namespace WordPress\Di;

use WordPress\DataModel\CustomTypeManager;
use WordPress\Env;
use WordPress\Post\Factory as PostFactory;
use WordPress\Support\RequestContext;

class FactoryDefault extends \WordPress\DI
{
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->setShared('customTypes', new CustomTypeManager);
		$this->setShared('postFactory', new PostFactory);
		$this->setShared('requestContext', new RequestContext);
	}
	
}
