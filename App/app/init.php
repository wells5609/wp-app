<?php

// Add feature support
AppApi::instance()
	->add_support('postx')
	->add_support('meta');


// Register some managers
// Managers for postx and meta are preloaded when added above
ManagerRegistry::instance()
	->register('reserve',	null);		# corresponds to ReserveManager
//	->register('companyReserves');		# corresponds to CompanyReservesManager


PostxManager::instance()
	->register_type('company');		# corresponds to post extension for post-type 'company'
	

MetaManager::instance()
	->register_type('company');		# corresponds to meta for post-type 'company'


ReserveManager::instance()
	->register_type('reserve');		# corresponds to post extension for post-type 'reserve'