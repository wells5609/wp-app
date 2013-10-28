<?php
/*
* HTML_Unordered_List
*
* Makes <ul>'s of <li>'s
*/


class HTML_UnorderedList extends HTML_Element {
	
	public $title;
	public $items = array();
	private $ordered = false;
	
	function __construct(){
		parent::__construct('ul');	
	}
	
	// set the title and tag
	public function title($title_text, $tag = 'h3', $attributes = NULL) {
		$Title = new HTML_Element($tag);
		$Title->setContent($title_text);
		$Title->setAttributes($attributes);
		$this->title = $Title;
		return $this->title;
	}
	
	/** setIsOrdered
	*
	* Whether this should be an ordered list <ol>
	* Not called by default and element is unordered <ul>
	*/
	public function setIsOrdered($bool){
		$this->ordered = (bool) $bool;
		if ( $this->ordered )
			$this->setTag('ol');
		return $this;
	}
		
	
	// create a <li> HTML_Element object
	public function li($text = false) {
		$li = new HTML_List_Item($text);
		$this->items[] = $li;
		if ( ! $text )
			return $li;
		return $this;
	}
	
	// create a bunch of <li> HTML_Element objects
	public function liArray(array $lis) {	
		foreach($lis as $li)
			$this->li($li);
		return $this;
	}
	
	// Set CSS classes for all <li> at once
	public function liClasses($classes) {
		foreach($this->lis as $li)
			$li->addClasses($classes);	
		return $this;
	}
	
	function prepare(){
		$str = '';
		foreach($this->items as $li)
			$str .= $li->render('r');
		$this->setContent($str);
	}
	
	function before(){
		if ( isset($this->title) )
			return $this->title->render('r');
	}
	
}


class HTML_List_Item extends HTML_Element {
	
	function __construct($content = false, $attributes = array() ){
		parent::__construct('li');
		if ( $content )
			$this->setContent($content);
		if ( ! empty($attributes) ){
			$this->setAttributes($attributes);	
		}
	}
		
}

?>