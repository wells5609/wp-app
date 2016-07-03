<?php

namespace WordPress\Utility\Xml;

use XMLWriter;
use Util\Typecast;
use Util\Sanitize;

class Document
{
	
	public $root = 'XML';
	public $version = '1.0';
	public $encoding = 'UTF-8';
	
	/**
	 * @var \XMLWriter
	 */
	protected $writer;
	
	/**
	 * @var array
	 */
	protected $data;
	
	public function __construct(XMLWriter $writer = null, $data = null) {
		$this->writer = $writer ?: new XMLWriter();
		$this->data = isset($data) ? $this->setData($data) : [];
	}
	
	public function setData($data) {
		$this->data = Typecast::toArray($data);
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function __toString() {
		$this->writer->openMemory();
		$this->writer->startDocument($this->version, $this->encoding);
		$this->writer->startElement($this->root);
		foreach($this->data as $key => $value) {
			$this->writeElement($key, $value);
		}
		$this->writer->endElement();
		$this->writer->endDocument();
		return $this->writer->outputMemory(true);
	}
	
	protected function writeElement($key, $value) {
	
		$key = $this->sanitizeKey($key);
		
		if (is_object($value)) {
			$value = Typecast::toArray($value);
		}
		
		if (is_array($value)) {
			$this->writeArrayElement($key, $value);
		} else if (is_scalar($value)) {
			$this->writeScalarElement($key, $value);
		}
	}
	
	protected function sanitizeKey($key) {
		$key = Sanitize::alnum($key);
		if (is_numeric($key)) {
			$key = "Item_" . $key;
		}
		return $key;	
	}
	
	protected function writeArrayElement($key, array $value) {
		if (isset($value['@tag'])) {
			$key = strval($value['@tag']);
			unset($value['@tag']);
		}
		$this->writer->startElement($key);
		if (isset($value['@attributes'])) {
			foreach(array_unique($value['@attributes']) as $attr => $attrVal) {
				$this->writer->writeAttribute($attr, $attrVal);
			}
			unset($value['@attributes']);
		}
		foreach($value as $k => $v) {
			$this->writeElement($k, $v);
		}
		$this->writer->endElement();
	}
	
	protected function writeScalarElement($key, $value) {
		$this->writer->writeElement($key, htmlentities(html_entity_decode($value), ENT_XML1|ENT_DISALLOWED));
	}
	
}
