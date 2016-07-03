<?php

namespace WordPress\Model\Post;

use WordPress\Model\Definition as BaseDefinition;
use WordPress\Model\Relationship\HasMany;
use WordPress\App;

class Definition extends BaseDefinition
{
	
	protected $relationships;
	
	public function initialize() {
		$metaRelation = new HasMany($this, model_definition('postmeta'));
		$metaRelation->setForeignKey('post_id');
		$this->addRelationship($metaRelation);
	}
	
	/**
	 * Returns the model's backend storage container.
	 * 
	 * @return \WordPress\Model\StorageInterface
	 */
	public function getStorage() {
		if (! isset($this->storage)) {
			$this->storage = new Storage();
		}
		return $this->storage;
	}
	
	final public function getName() {
		return 'post';
	}
	
	final public function getTableName() {
		return 'posts';
	}
	
	final public function getPrimaryKey() {
		return 'ID';
	}
	
	public function getClassName() {
		return 'WordPress\Model\Post\Post';
	}
	
	final public function getColumnMap() {
		return array(
			'ID' => 'ID',
			'post_author' => 'post_author',
			'post_date' => 'post_date',
			'post_date_gmt' => 'post_date_gmt',
			'post_content' => 'post_content',
			'post_title' => 'post_title',
			'post_excerpt' => 'post_excerpt',
			'post_status' => 'post_status',
			'comment_status' => 'comment_status',
			'ping_status' => 'ping_status',
			'post_password' => 'post_password',
			'post_name' => 'post_name',
			'to_ping' => 'to_ping',
			'pinged' => 'pinged',
			'post_modified' => 'post_modified',
			'post_modified_gmt' => 'post_modified_gmt',
			'post_content_filtered' => 'post_content_filtered',
			'post_parent' => 'post_parent',
			'guid' => 'guid',
			'menu_order' => 'menu_order',
			'post_type' => 'post_type',
			'post_mime_type' => 'post_mime_type',
			'comment_count' => 'comment_count'
		);
	}
	
}
