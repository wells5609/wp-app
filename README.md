WP-App
=====

RAD "framework" for WordPress.

This plugin allows you to extend WordPress and create new data objects in non-core tables.


##Basics

The plugin has a few primary components:

1. Schemas - define database tables and the objects they store. Each Schema defines a table.
2. Models - manipulates data (e.g. queries database). Each Model uses a Schema.
3. Objects - a representation of a data object (i.e. a table row).
4. Registries - store components of a particular type (e.g. there is one Schema Registry and one Model Registry).
5. Managers - control your various types of data models (e.g. registering and getting).

The components are generally used from the bottom up - you'll often call Manager and Registry methods, sometimes call Object and Model methods, and rarely call Schema methods (directly, that is).

###Schemas

Schemas define database tables using an SQL-like format:

```php

class Company_Schema extends Schema {
        
        public $table_basename = 'companies';
        
        public $field_names = array(
                'id'                    => "bigint(20) NOT NULL",
                'ticker'                => "varchar(8) NOT NULL default ''",
                'cik'                   => "varchar(32) default NULL",
                'exchange'              => "varchar(8) default NULL",
                'sic'                   => "int(8) default NULL",
                'sector'                => "varchar(32) default NULL",
                // ...
        );
        
        public $primary_key = 'id';
        
        public $unique_keys = array(
                'ticker'                => 'ticker',
                'cik'                   => 'cik',
        );
        
        public $keys = array(
                'exchange'              => 'exchange',
                'sic'                   => 'sic',
                'sector'                => 'sector',
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
                'ticker' => 'this.ticker_was_updated',
        );
        
        function before_insert() {
                // do something before inserting
        }
        
        function ticker_was_updated(){
                echo 'The ticker was just updated!';
        }
        
}

// The following is just illustrative - you should *not* create objects using the 'new' keyword:

$my_schema = new My_Schema();
$my_model = new My_Model( $my_schema );

// $my_object will be an instance of 'My_Object' (as defined above)
$my_object = $my_model->get_row( "SELECT * FROM {$my_schema->table} WHERE id = 2" );


```

