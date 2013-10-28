<?php
/*
* HTML_Input
*
* Creates inputs for forms
*/

class HTML_Input extends HTML_Element {
	
	public
		$type = 'text',
		
		$error = false,
		$label = false,
		
		$in_group = true,
		
		$help_text = false,
		$help_text_block = false,
		
		// selects/other inputs with choices
		$is_multiple = false,
		$selected = NULL,
		$options = array();
		
	// Accepted input types	
	private $_valid_types = array(
		'textarea',
		'text',
		'select',
		'checkbox',
		'hidden',
	);
	
	// Input "types" that are actually element names 
	// e.g. <select>, <textarea>
	private $_tag_types = array(
		'select',
		'textarea',
	);
	
	function __construct($type){
		if ( ! $this->_checkType($type) )
			return "Invalid type {$type}";
		
		$this->type = $type;
		
		if ( in_array($this->type, $this->_tag_types) )
			parent::__construct($this->type);
		else {
			parent::__construct('input');
			$this->setAttr('type', $this->type);
		}
		
		if ( 'hidden' == $this->type )
			$this->addClass('hidden');
	
		$this->extendAttributes(array('name' => NULL, 'value' => ''));
	}
	
	private function _checkType($type){
		if ( ! in_array($type, $this->_valid_types) )
			return false;
		return true;
	}
	
	public function get($var){
		if ( 'name' == $var || 'value' == $var )
			return $this->getAttr($var);
		else
			return $this->$var;
	}
	
	public function set($name, $value){
		if ( 'name' == $name || 'value' == $name )
			$this->setAttr($name, $value);
		else
			$this->$name = $value;
		return $this;
	}
	
	public function setOptions($options){
		$this->set('options', $options);
		return $this;
	}
	
	private function getSelected(){
		
		if ( ! empty($this->selected) )
			return $this->selected;
		
		elseif ( isset($_POST[$this->get('name')]) )
			return $_POST[$this->get('name')];
		
		return;
	}
	
	function prepare(){
		
		switch($type) {
			
			case 'select':
				$this->setAttr('value', $this->getSelected());
				if ( $this->is_multiple ) {
					$this->setAttr('multiple', 'multiple');	
					$this->setAttr('name', $this->get('name') . '[]');
				}
				$select = '';
				$select .= '<option value="0">' . ($this->get('label') ? 'Select a ' . $this->get('label') : '--- Select ---') . '</option>';
				foreach( $this->options as $option => $value ) :
					$select .= '<option value="' . $value . '"';
					if ( $value == $this->getSelected() ) {
						$select .= ' selected="selected"';
					}
					$select .= '>' . $option . '</option>';
				endforeach; 
				$this->setContent($select);
				break;
		
			case 'textarea':
				$this->setAttr('cols', '5');
				$this->setAttr('rows', '6');
				break;
			
			default:
				break;
		}
		
	}
	
	function before(){
		$s = '';
		
		if ( $this->in_group )
			$s .= '<div class="control-group' . ($this->get('error') ? ' error' : '') . '">';		
		
		if ( $this->get('label') )
			$s .= '<label for="' . $this->get('name') . '">' . $this->get('label') . '</label>';
		
		if ( $this->in_group )
			$s .= '<div class="controls">';
		
		return $s;	
	}
	
	function after(){
		$s = '';
		
		if ( $this->get('help_text') )
			$s .= '<span class="help-inline">' . $this->get('help_text') . '</span>';
		
		if ( $this->get('error') || $this->get('help_text_block') ) {
			if ( $this->get('help_text_block') )
				$text = $this->get('help_text_block');
			if ( $this->get('error') )
				$text = $this->get('error');
			$s .= '<span class="help-block">' . $text . '</span>';
		} 
			
		if ( $this->in_group )
			$s .= '</div></div>';
			
		return $s;
	}	
		
}
?>