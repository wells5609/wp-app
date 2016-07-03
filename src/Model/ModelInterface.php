<?php

namespace WordPress\Model;

interface ModelInterface extends \Serializable
{
	
	/**
	 * Constructor.
	 * 
	 * @param mixed $data [Optional]
	 * @return void
	 */
	public function __construct($data = null);
	
	/**
	 * Returns the model's name.
	 * 
	 * @return string
	 */
	public function getModelName();
	
	/**
	 * Returns the model's database table name, including any prefix.
	 * 
	 * Default is the model name.
	 * 
	 * @return string
	 */
	#public function getTableName();
	
	/**
	 * Returns the model's primary key.
	 * 
	 * Default = "id"
	 * 
	 * @return string
	 */
	public function getPrimaryKey();
	
	/**
	 * Returns a map of properties to columns.
	 * 
	 * @return array
	 */
	public function getColumnMap();
	
	/**
	 * Checks if the model has a database table column with the given name.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function isColumn($name);
	
	/**
	 * Checks whether the model has a property with the given name.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function isProperty($name);
	
	/**
	 * Translates a property to its corresponding column.
	 * 
	 * @param string $property
	 * @return string|null
	 */
	public function propertyToColumn($property);
	
	/**
	 * Translates a column to its corresponding property.
	 * 
	 * @param string $column
	 * @return string|null
	 */
	public function columnToProperty($column);
	
	/**
	 * Returns a property value by property or column name.
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function readAttribute($name);
	
	/**
	 * Assigns a property value by property or column name.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function writeAttribute($name, $value);
	
	/**
	 * Imports data into the object.
	 * 
	 * @param mixed $data
	 * @return void
	 */
	public function import($data);
	
	/**
	 * Returns the model's data as an array.
	 * 
	 * @return array
	 */
	public function toArray();
	
	/**
	 * Returns an array of the model's table data.
	 * 
	 * @return array
	 */
	public function getModelData();
	
	/**
	 * Returns the value of the primary key.
	 * 
	 * @return mixed
	 */
	public function getPrimaryKeyValue();
	
	/**
	 * Returns the related records of the given model.
	 * 
	 * @param string $type
	 * @return mixed
	 */
	public function getRelatedRecords($type);
	
	/**
	 * Saves the record to the database.
	 * 
	 * @param array $data [Optional]
	 * @return boolean
	 */
	public function save(array $data = null);
	
	/**
	 * Insert the record into the table.
	 * 
	 * @param array $data [Optional]
	 * @return mixed
	 */
	public function insert(array $data = null);
	
	/**
	 * Update the record in the table.
	 * 
	 * @param array $data [Optional]
	 * @return mixed
	 */
	 public function update(array $data = null);
	
	/**
	 * Delete the record from the table.
	 * 
	 * @return mixed
	 */
	public function delete();
	
	/**
	 * Creates a new instance of the model.
	 * 
	 * @param mixed $data
	 * @return \WordPress\Model\ModelInterface
	 */
	public static function forgeObject($data);
	
	/**
 	 * Queries the database using multiple fields (i.e. column)
	 *
	 * @param array $where Array of "column" => "value" args
	 * @param string $select The SQL "SELECT" string
	 * @return mixed
	*/	
	public static function find(array $where);
	
	/**
 	 * Queries the database using multiple fields (i.e. column)
	 *
	 * @param array $where Array of "column" => "value" args
	 * @param string $select The SQL "SELECT" string
	 * @return mixed
	*/	
	public static function findOne(array $where);
	
}
