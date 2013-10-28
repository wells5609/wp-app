WP-App
=====

RAD "framework" for WordPress.

This plug-in allows you to extend WordPress by creating new data objects in non-core tables.


#Basics

The plugin has a few primary components:

1. Schemas - define database tables and the objects they store. Each Schema defines a table.
2. Models - manipulates data (e.g. queries database). Each Model uses a Schema.
3. Objects - a representation of a data object (i.e. a table row).
4. Registries - store components of a particular type (e.g. there is one Schema Registry and one Model Registry).
5. Managers - control your various types of data models (e.g. registering and getting).

The components are generally used from the bottom up - you'll often call Manager and Registry methods, sometimes call Object and Model methods, and rarely call Schema methods (directly, that is).

##Schemas

Schemas define database tables using an SQL-like format. They are basically static includes, with a couple helper methods.

Example:

```php

class My_Schema extends Schema {
        
        public $table_basename = 'mydata';
        
        public $field_names = array(
                'id'                    => "bigint(20) NOT NULL",
                'name'                  => "varchar(50) NOT NULL default ''",
                'url'                   => "text default NULL",
                'type'                  => "varchar(24) NOT NULL",
                // ...
        );
        
        public $primary_key = 'id';
        
        public $unique_keys = array(
                'name'                => 'name',
                'url'                 => 'url',
        );
        
        public $keys = array(
                'type'                => 'type',
                'type__id'            => 'type, id',
                'type__name'          => 'type, name',
                // ...
        );
        
}

```

The Schema class (abstract) has two methods:

* `get_field_format($name)` - returns the sprintf-like format string used in `$wpdb->prepare()` calls: `%d` (int), `%f` (float), or `%s` (string) [default].
* `get_field_length($name)` - returns the maximum number of characters allowed for a field. e.g. in Company_Schema above, a `sector` would return `32`.


###Models

Models do most of the heavy lifting, such as querying the database and creating Objects from results. They require a Schema for construction.

They are essentially a table-specific $wpdb - that is, you can use most wpdb methods (e.g. `query`, `insert`, `get_var`, etc.) through the model itself, without having to specify the DB table explicitly.

When querying through a model, calls to the `get_row()` method will create an Object. The Object used will be determined by the Model variable `$_object_class`. Objects are created via the Model's `forgeObject()` method.

Models can have hook actions defined to be called before/after a particular action is performed. See the base Model class for a list of all available hooks.

Example:

```php

class My_Model extends Model {
        
        public $_object_class = 'My_Object';
        
        protected $before_insert = array(
                'call_this_function',
                array('Some_Class', 'call_this_method'),
                'this.before_insert',   // will call $this->before_insert()
        );
        
        protected $after_update_field = array(
                'url' => 'this.url_was_updated',
        );
        
        function before_insert() {
                // do something before inserting
        }
        
        function url_was_updated(){
                echo 'The URL was just updated!';
        }
        
}

// NOTE: the following is just illustrative - you should *not* create objects using the 'new' keyword!

$my_schema = new My_Schema();
$my_model = new My_Model( $my_schema );

// $my_object will be an instance of 'My_Object' (as defined above)
$my_object = $my_model->get_row( "SELECT * FROM {$my_schema->table} WHERE id = 2" );


```

##Objects

Objects are arbitrary data objects, and by default have very little functionality. They are essentially containers for database rows.

Upon creation (i.e. via its model's `forgeObject()` call), each column (i.e. field) becomes an Object variable.

Objects have a constructor (which can be overwritten) and 3 magic methods: `__get()`, `__set()`, and `__isset()`.

Below is an example of the Postx (Post extension) Object:

```php


class Postx_Object extends Object {
	
	public $post;
	
	/**
	* Sets up object from db result and uses $wp_post param 
	* or global $post to set post property.
	*/
	function __construct( &$db_object, &$wp_post = null ){
		
		global $post;
		
		// this is copied from abstract Object constructor
		foreach($db_object as $key => $val){
			$this->$key = $val;	
		}
		
		// this is the custom constructor functionality
		
		if ( null !== $wp_post ){
			$this->setPost($wp_post);
		}
		// current post? Avoid the get_post() call
		elseif ( $post->ID == $this->id ){
			$this->setPost($post);
		}
		else {
			$this->setPost($post->ID);
		}
		
	}
	
	// allow access to post fields.
	// e.g. $this->__get('post_name') => $this->post->post_name
	function __get( $var ){
	
		if ( strpos($var, 'post_') === 0 ){
			return isset($this->post->$var) ? $this->post->$var : NULL;	
		}
		return isset($this->$var) ? $this->$var : NULL;	
	}
	
	// Sets the post using an ID or object
	protected function setPost(&$post){
		
		if ( is_object($post) ){
			$this->post =& $post;
		}
		elseif ( is_numeric($post) ){
			$this->post =& get_post($post, OBJECT);
		}
	}
	
	public function get_post_field( $name ){
		return isset($this->post->$name) ? $this->post->$name : NULL;	
	}
}


```

