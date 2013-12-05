#WP-App

WordPress plug-in that allows you to create new data objects in non-core tables.


##Basics

The plugin has a few primary components:

1. **Models** - defines data (e.g. table, columns, keys), manipulates data (e.g. queries database), and creates Objects.
2. **Objects** - a representation of a table row.
3. **Registry** - stores objects.
4. **App** - initiates app and controls features, etc.


##Models

Rach Model represents a database table for a particular 'datatype'. It defines the table using a SQL-like format:

```php

class My_Model extends Model {
        
        public $table_basename = 'mydatas';
        
        public $columns = array(
                'id'          => "bigint(20) NOT NULL auto_increment",
                'name'        => "varchar(32) NOT NULL default ''",
                'url'         => "varchar(120) default NULL",
                'date'        => "timestamp default 0",
                'active'      => "tinyint default 1",
                // ...
        );
        
        public $primary_key = 'id';
        
        public $unique_keys = array(
                'name'        => 'name',
                'url'         => 'url',
        );
        
        public $keys = array(
                'date'        => 'date',
                'name_date'   => 'name, date',
                // ...
        );
        
}

```

###Methods: Column Data

The `Model` class has two methods to get column data:

* **`get_column_format($name)`** - returns the sprintf-like format string used in `$wpdb->prepare()` calls: `%d` (int), `%f` (float), or `%s` (string) [default].
* **`get_column_length($name)`** - returns the maximum number of characters allowed for a field. e.g. in `My_Model` above, `My_Model::get_column_length('url')` would return `120`.


###Methods: Database Access

Models query the database and create Objects from the results. Database access methods generally emulate WPDB methods. Several methods also have added functionality.

Database methods - exactly the same as their WPDB counterparts - include:

* **`query( $sql )`**
* **`get_var( $query = null, $x = 0, $y = 0 )`**
* **`get_col( $query = null , $x = 0 )`**
* **`get_results( $string, $output_type = OBJECT )`** *

*`get_results()` now calls `forgeObject()`; see below.


#####Methods with actions

The following methods call an action before and after performing their query:

* **`insert( $data, $format = null )`**
* **`replace( $data, $format = null )`**
* **`update( $data, $where, $format = null, $where_format = null )`**
* **`delete( $where, $where_format = null )`**

The above methods use the same syntax as their corresponding `WPDB` methods, ommitting the `table` parameter and call the following actions:

1. **`before_*()`**
2. **`after_*()`**

Function parameters are passed by reference to modify data prior to databasing.

#####Non-WPDB methods

Two custom methods provide a simple way to perform common queries:

* **`query_by( $column, $column_where, $select = '*', $extra_where = array() )`**
* **`query_by_multiple(array $where, $select = '*')`**

#####The get_row() method

A special method that returns the model's corresponding `Object`:

* **`get_row( $query = null, $output = OBJECT, $y = 0 )`**

`get_row()` calls `$this->forgeObject($db_result)` using the query results to create the proper Object. `forgeObject()` looks like this:

```php

protected function forgeObject( &$db_object ){
			
	if ( !$db_object )
	        return false;
			
	$class = $this->_object_class;
			
	return new $class( $db_object );	
}
		
```

By default, `$_object_class` is set to `'Object'` - the base Object. Simply change this variable to use a custom Object class.


##Objects

Objects represent a specific set of data, usually a table row.

###Methods

* **`__construct( &$db_object )`** - The base Object constructor accepts 1 parameter, the database result object, and passes this to its `import()` method.
* **`import( &$vars )`** - this method imports the passed vars into the Object as properties. It then calls its `onImport()` method, which does nothing by default.
* **`__call( $func, $args )`** - this magic method handles nonexistant methods, including `get_*()`, `set_*()`, and `the_*()`, which return, set, and echo, respectively, an object property.


