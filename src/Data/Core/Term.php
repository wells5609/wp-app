<?php

namespace WordPress\Data\Core;

class Term extends AbstractModel
{

	/**
	 * Term ID.
	 *
	 * @var int
	 */
	public $term_id;
	
	/**
	 * The term's name.
	 *
	 * @var string
	 */
	public $name = '';
	
	/**
	 * The term's slug.
	 *
	 * @var string
	 */
	public $slug = '';
	
	/**
	 * The term's term_group.
	 *
	 * @var string
	 */
	public $term_group = '';
	
	/**
	 * Term Taxonomy ID.
	 *
	 * @var int
	 */
	public $term_taxonomy_id = 0;
	
	/**
	 * The term's taxonomy name.
	 *
	 * @var string
	 */
	public $taxonomy = '';
	
	/**
	 * The term's description.
	 *
	 * @var string
	 */
	public $description = '';
	
	/**
	 * ID of a term's parent term.
	 *
	 * @var int
	 */
	public $parent = 0;
	
	/**
	 * Cached object count for this term.
	 *
	 * @var int
	 */
	public $count = 0;
	
	public function getWordPressObjectType() {
		return 'term';
	}

	/**
	 * Returns the unique identifier for the model.
	 *
	 * @return int|string
	 */
	public function getId() {
		return $this->term_id;
	}
	
}
