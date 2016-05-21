<?php

namespace WordPress\Di;

use WordPress\Support\RequestContext;
use WordPress\Data\Post\Factory as PostFactory;
use WordPress\Data\Post\Repository as PostRepository;
use WordPress\Data\Taxonomy\Factory as TaxonomyFactory;
use WordPress\Data\Taxonomy\Repository as TaxonomyRepository;
use WordPress\Data\Term\Factory as TermFactory;
use WordPress\Data\Term\Repository as TermRepository;
use WordPress\Data\User\Factory as UserFactory;
use WordPress\Data\User\Repository as UserRepository;
use WordPress\Data\Manager as DataManager;

class FactoryDefault extends Container
{
	
	public function __construct() {
		
		parent::__construct();
		
		// Request context
		$this->setShared('requestContext', new RequestContext);
		
		// Posts
		$this->setShared('postFactory',	$post_factory = new PostFactory);
		$this->setShared('posts',		$post_repo = new PostRepository($post_factory));
		
		// Taxonomies
		$this->setShared('taxonomyFactory',	$tax_factory = new TaxonomyFactory);
		$this->setShared('taxonomies',		$tax_repo = new TaxonomyRepository($tax_factory));
		
		// Terms
		$this->setShared('termFactory',	$term_factory = new TermFactory);
		$this->setShared('terms', 		$term_repo = new TermRepository($term_factory));
		
		$this->setShared('userFactory', $user_factory = new UserFactory);
		$this->setShared('users',		$user_repo = new UserRepository($user_factory));
		
		// Data manager
		$this->setShared('dataManager', new DataManager($post_repo, $tax_repo, $term_repo, $user_repo));
	}
	
}
