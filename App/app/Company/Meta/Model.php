<?php

class Company_Meta_Model extends Meta_Model {
		
	/**
	* Column in the meta table which maps to a unique object identifier.
	*/
	public $id_column = 'post_id';
	
	public $_object_class = 'Company_Meta_Object';
	
	
}