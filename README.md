#WP-App

WordPress plug-in that allows you to create new data objects in non-core tables.


##Basics

The plugin has a few primary components:

1. Models - defines data (e.g. table, columns, keys), manipulates data (e.g. queries database), and creates Objects.
2. Objects - a representation of a table row.
3. Managers - manages Models & Objects of a particular type.
4. Registry - stores Models and Managers.


###Models

Models define the database table  using an SQL-like format:

```php

class My_Model extends Model {
        
        public $table_basename = 'mydatas';
        
        public columns = array(
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

The Model class has two methods to get column data:

* `get_column_format($name)` - returns the sprintf-like format string used in `$wpdb->prepare()` calls: `%d` (int), `%f` (float), or `%s` (string) [default].
* `get_column_length($name)` - returns the maximum number of characters allowed for a field. e.g. in My_Model above, `My_Model::get_column_length('url')` would return `120`.


Models also do most of the heavy lifting, such as querying the database and creating Objects from results.




