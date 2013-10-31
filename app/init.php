<?php

// Add feature support
AppApi::instance()
	->add_support('postx')
	->add_support('meta');


// Register some managers
Registry::instance()
	->register_manager('postx')		# corresponds to PostxManager
	->register_manager('meta');		# corresponds to MetaManager


PostxManager::instance()
	->register_type('company')		# post extension for post-type 'company'
	->register_type('reserve');	


MetaManager::instance()
	->register_type('company');		# meta for post-type 'company'
