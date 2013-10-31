<?php

// FUNCTIONS

function get_model( $type ){
		
	return Registry::instance()->get_manager($type)
		->get_model( $type );
}

function get_models(){
	
	$Registry = Registry::instance();
	
	$models = array();
	
	foreach($Registry->get_managers() as $name => $manager){
		
		$models[ $name ] =& $manager->get_models();
	}

	return $models;
}

function get_schema( $type ){
	
	if ( $type instanceof Model )
		return $type->get_schema();
	
	return Registry::instance()
		->get_model( $type )->get_schema();
}
	
function get_schemas(){
	
	$Registry = Registry::instance();
	
	$schemas = array();
	$models =& get_models();
	
	foreach( $models as $name => $type ){
		
		foreach($type as $_t => $model){
			
			$key = ($name == $_t) ? $name : $_t . ':' . $name;
			
			$schemas[ $key ] =& $model->get_schema();
		}
	}
	
	return $schemas;	
}


// Meta

function get_meta_model( $type ){
	
	return MetaManager::instance()->get_model($type);	
}

function get_meta_object( $type, $id, $meta_key = null, $output = OBJECT ){
	
	if ( $type instanceof Meta_Model ){
		$model = $type;	
	}
	else {
		$model = MetaManager::instance()->get_model($type);
	}
	
	return $model->get_object($id, $meta_key, $output);
}


function get_xmeta_value( $type, $id, $meta_key = null ){
	
	if ( $type instanceof Meta_Model ){
		$model = $type;	
	}
	else {
		$model = MetaManager::instance()->get_model($type);
	}
	
	return $model->get_value($id, $meta_key);
}


// Postx

function get_postx_model( $type ){
	
	return PostxManager::instance()->get_model($type);
}

function get_postx( $post = null ){
	
	return PostxManager::instance()->get_postx($post);
}
