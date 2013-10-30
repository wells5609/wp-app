<?php

// Add feature support
AppApi::instance()
	->add_support('postx')
	->add_support('meta');


// Register some managers
//Registry::instance()
//	->register('Manager', 'postx')		# corresponds to PostxManager
//	->register('Manager', 'meta')
//	->register('Manager', 'reserve');		
	
//	->register('Manager', 'companyReserves');


PostxManager::instance()
	->register_type('company');		# post extension for post-type 'company'
	

MetaManager::instance()
	->register_type('company');		# meta for post-type 'company'


ReserveManager::instance()
	->register_type('reserve');		# post extension for post-type 'reserve'