<?php
/*
* HTML_Attributes
*
* This is abstract
*/

abstract class HTML_Attributes {
	
	var $attributes = array( 
		'class' => array() 
	);
	
		
	/** ------------------------------ 
			Attribute Handling
	------------------------------- */
	
	/** getAttr
	*
	*	Returns attribute if set, otherwise false
	*/
		public function getAttr($name){
			return ( ! empty($this->attributes[$name]) ? $this->attributes[$name] : false );
		}
		
		
	/** setAttr
	*
	*	Sets an attribute
	*/
		public function setAttr($name,$value){
			if ( strstr($value, ' ') )
				$value = @explode(' ', $value);
			$this->attributes[$name] = $value;	
			return $this;
		}
		public function addAttr($name,$value){
			$this->setAttr($name, $value);
			return $this;
		}
		
		
	/** hasAttr
	*
	*	Whether element has the attribute specified
	*/
		public function hasAttr($name){
			return ( ( isset($this->attributes[$name]) && ! empty($this->attributes[$name]) ) ? true : false );
		}
	
		
	/** setAttribute
	*
	*	Sets an attribute
	*/
		public function setAttribute($name,$value){
			return $this->setAttr($name, $value);
		}
		
		
	/** setAttributes
	*
	*	Sets element attributes given array or string.
	*
	*	e.g.		$attrs	=	array('id' => 'this', 'href' => 'that');
	*	same as:	$attrs	=	'id="this" href="that"'
	*/
		public function setAttributes($attrs = NULL){
			if ( NULL === $attrs )
				return;
			
			if ( is_string($attrs) ){
				$attrs = $this->parseArgs($attrs);
			}
			// attrs should now be array
			if ( ! is_array($attrs) )
				return;
			// set attributes from attrs array
			foreach($attrs as $k => $v)
				$this->attributes[$k] = $v;
			
			return $this;
		}
	
	
	/** getAttributes
	*
	*	Returns all set attributes
	*/
		public function getAttributes(){
			return $this->attributes;
		}
	
	
	/** hasAttributes
	*
	*	Whether element has any attributes
	*/
		public function hasAttributes(){
			if ( empty($this->attributes['class']) && count($this->attributes) < 2 )
				return false;
			return true;
		}
	
	/** parseArgs
	*
	*/
	public function parseArgs($attrs){
			
		// string is only 1 attribute => prefix with space
		if ( false === strpos($attrs, '" ') )
			$attrs = ' ' . $attrs;
		
		// split string at '" '
		$attributes = explode('" ', $attrs);
		$attribs = array();
		
		foreach($attributes as $attr) :
			// split attr name and value
			$keyVals = explode('="', $attr);
			$key = @array_shift($keyVals);
			$vals = $keyVals;
			
			foreach($vals as $val):
				// remove quotes from attr value
				$val = str_replace('"', '', $val);
				// if spaces, has multiple values (e.g. class)
				if (strstr($val, ' '))
					$val = explode(' ', $val);
				$attribs[ $key ] = $val;
			endforeach;
		endforeach;
		
		return $attribs;
	}
	
	/** extendAttributes
	*
	*	Allows child classes to add extra attributes without defining values.
	*	Useful for elements that want to require certain attributes.
	*/
		public function extendAttributes(array $attributes){
			foreach($attributes as $attr => $default)
				$this->attributes[$attr] = $default;
		}
	
		
	/** ----------------------- 
			CSS Classes
	------------------------ */
	
	// addClass
		public function addClass($name){
			if (is_string($class = $this->attributes['class'])){
				$this->attributes['class'] = array($class);	
			}
			$this->attributes['class'][] = $name;
			return $this;
		}
	
	// hasClass
		public function hasClass($name){
			return ( isset($this->attributes['class'][$name]) && ! empty($this->attributes['class'][$name]) ) ? true : false;
		}
	
	// getClasses
		public function getClasses(){
			return $this->getAttr('class');
		}
	
	// addClasses
		public function addClasses($classes){
			if ( is_array($classes) ) {
				foreach($classes as $class)
					$this->addClass($class);
			}
			else if ( ! is_null($classes) )
				$this->addClass($classes);
			return $this;
		}
	
	// hasClasses
		public function hasClasses(){
			return ( ! empty($this->attributes['class']) ) ? true : false;
		}
	
	
	/** -----------------------------------
			Printing/Generating Output
	------------------------------------ */
	
	/** attribute_str (static)
	*
	*	Returns formatted string value of an attribute
	*/
		static public function attribute_str($name, $value){
			if ( is_array($value) )
				$value = implode(' ', $value);
			
			if ( 'href' == $name || 'data-url' == $name )
				$value = esc_url($value);
			else
				$value = esc_attr($value);
				
			return ' ' . $name . '="' . $value . '"';
		}
		
	/** attributes_str (can be called static)
	*
	*	Returns string value of all attributes, except those
	*	in $exclude array.
	*/
		public function attributes_str($attributes, $exclude = array()){
			$s = ' ';
			foreach($attributes as $attr => $val) :
				if ( !empty($exclude) && in_array($attr, $exclude) )
					continue;
				$s .= self::attribute_str($attr, $val);
			endforeach;
			return $s;
		}

	/** attrStr
	*
	*	Returns formatted (HTML) string value of an attribute
	*/
		public function attrStr($attr){
			if ( ! $value = $this->getAttr($attr) )
				return '';
			return self::attribute_str($attr, $value);
		}
		
	/** attrsStr
	*
	*	Returns string value of all attributes, except those
	*	in $exclude array.
	*/
		public function attrsStr($exclude = array()){
			return $this->attributes_str($this->attributes, $exclude);
		}


	/** printAttributes
	*
	*	Echoes attributes except those in $exclude.
	*/
		function printAttributes($exclude = array()){
			echo $this->attrsStr($exclude);
		}
	
	
	/** printClasses
	*
	*	Echoes CSS classes string
	*/
		function printClasses(){
			echo @implode(' ', $this->attributes['class']);
		}
	
}

function html_parse_args($args){
	return HTML_Attributes::parseArgs($args);	
}

?>