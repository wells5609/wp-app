<?php

namespace WordPress\Admin;

class Accordion
{
	
	protected $id;
	protected $sections = array();
	protected $sectionTitles = array();
	
	public function __construct($id = null) {
		$this->id = $id;
		wp_enqueue_script('accordion');
	}
	
	public function addSection($name, $content, $title = null) {
		$this->sections[$name] = $content;
		if ($title) {
			$this->sectionTitles[$name] = $title;
		}
	}
	
	public function setSectionTitle($name, $title) {
		$this->sectionTitles[$name] = $title;
	}
	
	public function getSections() {
		return $this->sections;
	}
	
	public function getSection($name) {
		return isset($this->sections[$name]) ? $this->sections[$name] : null;
	}
	
	public function getSectionTitle($name) {
		if (isset($this->sectionTitles[$name])) {
			return $this->sectionTitles[$name];
		}
		return ucwords(str_replace(array('_', '-'), ' ', $name));
	}
	
	public function __toString() {
		
		$s = '<div class="accordion-container"'.(isset($this->id) ? ' id="'.$this->id.'"' : '').'>';
		$s .= '<ul class="outer-border">';
		
		$first = true;
		foreach($this->sections as $name => $content) {
			$s .= '<li class="control-section accordion-section '.$name.($first ? ' open' : '').'" id="'.$name.'">'
				. '<h3 class="accordion-section-title hndle" tabindex="0">'.$this->getSectionTitle($name).'</h3>'
				. '<div class="accordion-section-content">'.$content.'</div>'
				. '</li>';
			$first = false;
		}
		
		$s .= '</ul>';
		$s .= '</div>';
		
		return $s;
	}

}