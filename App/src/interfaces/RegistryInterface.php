<?php

interface RegistryInterface {
	
	function get($name = null);
	
	function classFromName($name);
		
}