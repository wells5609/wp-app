<?php

namespace WordPress\Admin\Settings;

class Field
{

	const TEXT = 'text';
	const NUMBER = 'number';
	const TEXTAREA = 'textarea';
	const CHECKBOX = 'checkbox';
	const RADIO = 'radio';
	const MULTICHECK = 'multicheck';
	const SELECT = 'select';
	const PASSWORD = 'password';
	const FILE = 'file';
	const COLOR = 'color';
	const WYSIWYG = 'wysiwyg';
	const HTML = 'html';
	
	public $name;
	public $label;
	public $desc = '';
	public $type = self::TEXT;
	public $default = '';
	public $size;
	public $sanitize_callback = '';
	public $options = array();
	
}
