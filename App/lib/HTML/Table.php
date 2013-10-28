<?php
/*
* HTML_Table
*
* A class to create HTML tables.
*
* This file includes 3 classes:
*	HTML_Table - Only class that should be instantiated by the user.
*	HTML_Table_Row - A table row (<tr>), created by HTML_Table.
*	HTML_Table_Cell - A table cell (<td> or <th>), created by HTML_Table_Row.
*/

class HTML_Table extends HTML_Element {

	var $rows = array();
	var $thead;
	var $tfoot = false;
	var $is_sortable = false;
	
	private static $_using_wp = false;
	
	
	function __construct(){
		parent::__construct('table');
		if ( function_exists('wp_register_script') && defined("SITE_BASE_URL") ){
			self::$_using_wp = true;	
			wp_register_script('jquery-tablesorter', SITE_BASE_URL . '/inc/classes/HTML/assets/jquery.tablesorter.min.js', array('jquery'), false, true);
		}
	}
	
	
	/** row
	*
	* Adds a row of data to the table.
	*
	* @param	array|bool	$cells		Array of cells (strings) to add to row.	
	* @param	null|array	$attributes	Array of HTML attributes to add to row.
	* @param 	bool		$is_thead	Is row is a table header?
	* @return	object					if $cells = false returns HTML_Table_Row, else HTML_Table.
	*/
	public function row($cells = false, $attributes = NULL, $is_thead = false) {
		$row = new HTML_Table_Row($cells);
		$row->setAttributes($attributes);
	
		if ($is_thead) {
			$row->setIsHead($is_thead);
			$this->thead = $row;
		}
		else
			$this->rows[] = $row;
	
		// no row content => return HTML_Table_Row object
		if ( ! $cells )
			return $row;
	
		return $this;
	}
	
	
	/** thead
	*
	* Sets the table header.
	*
	* @param	bool|array	$cells		Array of cells to add to header row.
	* @param	array|NULL	$attributes	Array of attributes to add to row.
	* @return	object					HTML_Table_Row if $cells = false, otherwise HTML_Table.
	*/	
	public function thead($cells = false, $attributes = NULL) {
		return $this->row($cells, $attributes, true);
	}
	
	
	/** tfoot
	*
	* Whether the thead should also be appended as <tfoot>.
	*
	* @param	bool	$bool	true/false.
	* @return	object			HTML_Table
	*/	
	public function tfoot($bool){
		$this->tfoot = (bool) $bool;
		return $this;
	}
	
	
	/** setIsSortable
	*
	* Whether the table should be sortable via JavaScript.
	*
	* @param	bool	$bool	true/false.
	* @return	object			HTML_Table
	*/	
	public function setIsSortable($bool = true){
		$this->is_sortable = (bool) $bool;
		if ( $this->is_sortable && self::$_using_wp ) {
			wp_enqueue_script('jquery-tablesorter');
			$this->addClass('table-sortable');
		}
		return $this;
	}
	
	
	/** prepare
	*
	* Prepares table rows and their cells for output.
	*
	* @return	void
	*/	
	public function prepare(){
		$s = '';
		if ( $this->thead ) {
			$this->thead->prepare();
			$headStr = $this->thead->__toString();
			$s .= '<thead>' . $headStr . '</thead>';
		}
		foreach ( $this->rows as $row ){
			$row->prepare();
			$s .= $row->__toString();
		}
		if ( $this->tfoot && $this->thead )
			$s .= '<tfoot>' . $headStr . '</tfoot>';
		$this->setContent($s);
	}


	/** populate
	*
	* Creates a table from an array of "rows", each of which
	* is an array of "cells".
	*
	* @param	array	$data	The nested array of data
	* @return	object			HTML_Table
	*/	
	public function populate(array $data) {
		$this->data = $data;
		foreach($this->data as $row)
			$this->row($row);
		return $this;
	}
	
}


/** HTML_Table_Row
*
* A row of data.
*/	
class HTML_Table_Row extends HTML_Element {
	
	var $cells = array();
	var $is_thead = false;
	
	function __construct($cells = false) {
		parent::__construct('tr');
		if ($cells) {
			if ( is_array($cells) ) {
				foreach($cells as $cell)
					$this->cell($cell);
			}
			else $this->cell($cells);	
		}
	}
	
	/** setIsHead
	*
	* Set the row as a table header.
	* 
	* @param	bool	$boolean
	* @return	object	HTML_Table_Row
	*/	
	public function setIsHead($boolean) {
		$this->is_thead = (bool) $boolean;
		return $this;
	}
	
	
	/** cell
	*
	* Adds a cell to the row.
	*
	* @param	string|NULL	$content	String of cell contents.
	* @return	object					HTML_Table_Cell if $content = false, otherwise HTML_Table_Row.
	*/	
	public function cell($content = NULL) {
		$cell = new HTML_Table_Cell($content);
		$this->cells[] = $cell;
		// no content => return cell object
		if (!$content)
			return $cell;
		return $this;
	}
	
	/** prepare
	*
	* Prepares table row's cells for output.
	*
	* @return	void
	*/	
	public function prepare(){
		$s = '';
		foreach($this->cells as $cell) :
			if ($this->is_thead)
				$cell->setTag('th');
			$s .= $cell->__toString();
		endforeach;
		$this->setContent($s);
	}
	
}


/** HTML_Table_Cell
*
* A simple extension of the HTML_Element class to create
* table cells. Default tag is 'td'.
*/	
class HTML_Table_Cell extends HTML_Element {
	
	function __construct($content = NULL, $attributes = NULL){
		parent::__construct('td'); // default td
		if ($content)
			$this->setContent($content);
		if ($attributes)
			$this->setAttributes($attributes);
	}
		
}

?>