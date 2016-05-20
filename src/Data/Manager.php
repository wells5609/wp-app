<?php

namespace WordPress\Data;

use WordPress\Data\Post\Type as PostType;
use WordPress\Data\Post\Repository as PostRepository;
use WordPress\Data\Taxonomy\Type as Taxonomy;
use WordPress\Data\Taxonomy\Repository as TaxonomyRepository;
use WordPress\Data\Term\Repository as TermRepository;
use WordPress\Database\Table\Schema;
use WordPress\Database\Repository as DatabaseRepository;
use InvalidArgumentException;

class Manager
{
	
	protected $repositories = array();
	protected $customPostTypes = array();
	protected $customTaxonomies = array();
	
	public function __construct(
		PostRepository $postRepository, 
		TaxonomyRepository $taxonomyRepository,
		TermRepository $termRepository
	) {
		$this->addRepository($postRepository);
		$this->addRepository($taxonomyRepository);
		$this->addRepository($termRepository);
	}
	
	public function addRepository(RepositoryInterface $repository) {
		$this->repositories[$repository->getEntityTypeName()] = $repository;
	}
	
	public function getRepository($type) {
		if ($type instanceof EntityInterface) {
			return $type->getRepository();
		}
		return isset($this->repositories[$type]) ? $this->repositories[$type] : null;
	}
	
	public function register($object) {
		if ($object instanceof PostType) {
			$this->registerPostType($object);
		#} else if ($object instanceof Taxonomy) {
		#	$this->registerTaxonomy($object);
		} else if ($object instanceof Schema) {
			$this->registerDataType($object);
		} else {
			throw new InvalidArgumentException("Invalid data type: ".get_class($object));
		}
	}
	
	public function registerDataType(Schema $schema, DatabaseRepository $repository = null) {
		if (! isset($repository)) {
			$repository = new DatabaseRepository($schema);
		}
		$this->repositories[$schema->name] = $repository;
	}
	
	public function registerPostType(PostType $type) {
		$type->register();
		if (isset($type->class)) {
			$this->getRepository('post')->getFactory()->setClass($type->slug, $type->class);
		}	
		$this->customPostTypes[$type->slug] = $type;
	}
	
	public function registerTaxonomy(Taxonomy $taxonomy) {
		$taxonomy->register();
		if (isset($taxonomy->class)) {
			$this->getRepository('taxonomy')->getFactory()->setClass($taxonomy->slug, $taxonomy->class);
		}
		$this->customTaxonomies[$taxonomy->slug] = $taxonomy;
	}
	
}
