<?php

namespace WordPress;

class Request extends Http\Request
{

	public function getQueryVar($key) {
		global $wp_query;
		return isset($wp_query->query_vars[$key]) ? $wp_query->query_vars[$key] : null;
	}

	public function __get($key) {
		$value = parent::__get($key);
		return null === $value ? $this->getQueryVar($key) : $value;
	}

}
