<?php

require_once 'Attributes.php';

/*
* HTML_Element
*
* A generic HTML element
*/

class HTML_Element extends HTML_Attributes {
	
	public $tag;
	
	public $content = null;
	
	public $isSelfClosing = false;
	
	public $isHtml5 = true;
	
	private $selfClosingTags = array(
		'hr', 'br', 'input', 'meta',
	);
	
	
	// constructor
	public function __construct($tag = false){
		if ($tag) $this->setTag($tag);
	}
	
	/** __get
	*
	*	Gets a variable value.
	*/
		public function __get($variable) {
			return $this->$variable;
		}
	
	/** __set
	*	
	*	sets a variable value
	*/
		public function __set($variable, $value) {
			$this->$variable = $value;
		}
		
	/** __isset
	*
	*	checks if a variable is set, returns true or false.
	*/
		public function __isset($variable) {
			if ( isset($this->$variable) && ! empty($this->$variable) )
				return true;
			return false;	
		}
	
	// Sets element tag
	public function setTag($tag){
		$this->tag = $tag;
		$this->isSelfClosing = in_array($this->tag, $this->selfClosingTags);		
		return $this;
	}
	
	// Is this a self-closing tag?
	public function isSelfClosing(){
		return (bool) $this->isSelfClosing;
	}
	
	// Should element use HTML5? (boolean)
	public function setHtml5($bool){
		$this->isHtml5 = (bool) $bool;
		return $this;	
	}
	
	// Is element using HTML5 standard?
	public function isHtml5(){
		return (bool) $this->isHtml5;
	}
	
	// Sets element content (string)
	public function setContent($content){
		if ( ! $this->isSelfClosing() )
			$this->content = (string) $content;	
		return $this;
	}
	
	// Does element have content?
	public function hasContent(){
		return !empty($this->content) ? true : false;
	}
	
	// Returns element content (string)
	public function getContent(){
		return $this->content;	
	}
	
	public static function tag($tag, $content = '', $attributes = array(), $is_html5 = true){
		$s = "<{$tag}";
		if ( !empty($attributes) ){
			$parsed = HTML_Attributes::parseArgs($attributes);	
			$s .= HTML_Attributes::attributes_str($parsed);
		}
		$s .= ">";
		if ( !empty($content) ){
			$s .= $content;	
		}
		$s .= "</{$tag}";
	}
	
	
	/**	__toString
	*
	*	Generates string from HTML_Element object (or child)
	*
	*	Runs prepare() method to setup element (usually its content)
	*	Runs before() returns string, prepended to element
	*	Runs after() returns string, appended to element
	*	If $isHtml5 = true, self-closing tags close with '>' rather than '/>'
	*/
	public function __toString(){
		$s = '';	
		
		if ( is_callable(array($this, 'prepare')) )
			$this->prepare();
			
		if ( is_callable(array($this, 'before')) )
			$s .= $this->before();
		
		if ( !isset($this->tag) )
			$this->tag = 'div';
		
		$s .= '<' . $this->tag;
		
		if ( $this->hasAttributes() )
			$s .= $this->attrsStr();
		
		if ( $this->isSelfClosing() ){
			if ( $this->isHtml5() )
				$s .= '>';
			else
				$s .= ' />';	
		}
		else {
			$s .= '>';
			
			if ( $this->hasContent() )
				$s .= $this->getContent();
			
			$s .= '</' . $this->tag . '>';	
		}
		
		if ( is_callable(array($this, 'after')) )
			$s .= $this->after();
		
		return $s;
	}
	
	/** render
	*
	*	Runs __toString() and prints string if $echo = true, 'echo', or 'e'
	*/
	public function render($echo = false){
		$str = $this->__toString();
		if ( true === $echo || 'echo' === $echo || 'e' === $echo )
			echo $str;
		return $str;
	}
	
	
}

?>