<?php

namespace WordPress\Admin\ListTable;

use InvalidArgumentException;

if (! class_exists('WP_List_Table')) {
	require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

class Table extends \WP_List_Table
{
	
	/**
	 * @var \WordPress\Admin\ListTable\Definition
	 */
	private $definition;
	
    /**
	 * Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     */
    public function __construct($definition) {
    	
		// Check that we were passed a list table definition
    	if (! $definition || ! $definition instanceof Definition) {
    		throw new InvalidArgumentException(sprintf(
    			'Expecting "%1$s"\\Definition, given: %2$s', __NAMESPACE__,
    			(is_object($definition) ? get_class($definition) : gettype($definition))
			));
    	}
		
		$definition->setListTable($this);
		
		parent::__construct(array(
			'singular' => $definition->singularName(),
			'plural' => $definition->pluralName(),
			'ajax' => $definition->enableAjax()
		));
		
		$this->definition = $definition;
	}
	
	public function getCurrentBulkAction() {
		return $this->current_action();
	}
	
	public function getCurrentPageNum() {
		return $this->get_pagenum();
	}
	
	/**
	 * Make row_actions() method publicly accessible.
	 */
	public function generateRowActions($actions) {
		return $this->row_actions($actions);
	}
	
	/**
	 * Prepare the table items and print the HTML.
	 */
	public function display() {
		
		$this->prepare_items();
		
		if ($this->definition->formEnable()) {
			echo '<form id="'.$this->definition->formId().'" method="'.$this->definition->formMethod().'">';
			echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'">';
		}
		
		echo $this->definition->displayBefore();
		parent::display();
		echo $this->definition->displayAfter();
		
		if ($this->definition->formEnable()) {
			echo '</form>';
		}
	}
	
    /** 
	 * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
	 * 
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     */
    public function get_columns() {
    	
    	$columns = $this->definition->columns();
		
		// Prepend checkbox 'cb' column if checkboxes are enabled
    	if ($this->definition->enableCheckboxes()) {
			$columns = array_merge(array('cb' => '<input type="checkbox" />'), $columns);
		}
		
		return $columns;
    }

	public function get_hidden_columns() {
		return $this->definition->hiddenColumns();
	}

    public function get_sortable_columns() {
    	return $this->definition->sortableColumns();
    }

    /** 
	 * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     */
    public function prepare_items() {
    	
		// The the 3 types of columns
        $this->_column_headers = array(
        	$this->get_columns(), 
        	$this->get_hidden_columns(), 
        	$this->get_sortable_columns()
		);
        
		// Process the current bulk action, if enabled
		if ($this->definition->enableBulkActions() && $this->getCurrentBulkAction()) {
	        $this->definition->processBulkAction($this->getCurrentBulkAction());
		}
		
		// Calculate the total number of items
        $total_items = count($this->definition->getItems());
		
		// Determine how many items to show on a page
		$per_page = $this->definition->itemsPerPage();
		
		// Calculate the total number of pages
		$total_pages = ceil($total_items/$per_page);
		
		// Set the current page items
		$this->items = $this->definition->getPagedItems($this->getCurrentPageNum());
        
		// Set the pagination arguments
        $this->set_pagination_args(compact('total_items', 'per_page', 'total_pages'));
    }

    /** 
	 * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
	 * 
     * @return string Text or HTML to be placed inside the column <td>
     */
    public function column_default($item, $column_name) {
    	
    	if (method_exists($this->definition, 'column'.$column_name)) {
    		return $this->definition->{'column'.$column_name}($item);
    	}
		
		if ($this->definition->debug()) {
			return $column_name.' = <pre>'.print_r($item, true).'</pre>';
		}
		
		return '<em style="color:red">Definition missing `<b>column'.ucfirst($column_name).'()</b>` method</em>';
    }

    /** 
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
	 * 
     * @param array $item 
	 * 		A singular item (one full row's worth of data)
	 * 
     * @return string 
	 * 		Text to be placed inside the column <td> (movie title only)
     */
    public function column_cb($item) {
        return sprintf(
        	'<input type="checkbox" name="%1$s[]" value="%2$s" />',
        	$this->definition->singularName(),
        	$this->definition->getCheckboxValue($item)
        );
    }

    /**
	 * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     */
    public function get_bulk_actions() {
    	return $this->definition->bulkActions();
    }

    /** 
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     */
    public function process_bulk_action() {
    	
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
        	wp_die('Items deleted (or they would be if we had items to delete)!');
		}
		
	}

}
