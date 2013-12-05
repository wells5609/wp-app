<?php
/** App API functions */

/**
* Registers a data type with Registry.
*/
function register_datatype( $type, $args = array() ){
	$Registry = Registry::instance();
	$Registry->register_datatype( $type, $args );	
}

/** ======== Models ======== */

/**
* Returns Model object instance of given datatype.
*/
function get_model( $type ){
	return Registry::instance()->get_model( $type );
}

/**
* Returns all Model object instances.
*/
function get_models(){
	return Registry::instance()->get_objects_of_type( 'model' );
}

/**
* Returns DB schema for Model of given datatype.
*/
function get_schema( $type ){
	if ( $type instanceof Model )
		return $type->get_schema();
	return Registry::instance()->get_model( $type )->get_schema();
}

/**
* Returns DB schemas for all datatype Models.
*/	
function get_schemas(){
	$schemas = array();
	$models =& get_models();
	foreach( $models as &$model ){
		$key = $model->table_basename;
		$schemas[ $key ] =& $model->get_schema();
	}
	return $schemas;	
}


/** ======== Meta ======== */

/**
* Returns meta Model object instance of given data type.
*/
function get_meta_model( $type ){
	return Registry::instance()->get_meta_model($type);	
}

/**
* Returns Meta Object for given data type, ID, and meta key.
*/
function get_meta_object( $type, $id, $meta_key = null, $output = OBJECT ){
	$model =& Registry::instance()->get_meta_model($type);
	return $model->get_object($id, $meta_key, $output);
}

/**
* Returns meta value for given data type, ID, and meta key.
*/
function get_xmeta_value( $type, $id, $meta_key = null ){
	return get_meta_object( $type, $id, $meta_key )->meta_value;
}


/** ======== Other ======== */

/**
* Returns Helper object for given datatype
*/
function get_helper( $type ){
	return Registry::instance()->get_helper( $type );
}

/** 
* Used in Registry to format class names from datatypes.
*/
function format_class_underscore($str){
	$class = ucwords(str_replace(array('-','_'), ' ', $str));
	$class = trim(str_replace(' ', '_', $class));
	return $class;
}


/** ======== Post Extension ======== */

function register_extended_datatype( $datatype, $post_type, $args = array() ){
	$Registry = Registry::instance();
	$args['extends_post_type'] = $post_type;
	$Registry->register_datatype( $type, $args );	
	return;
}

function get_postx( $post = null ){
	return PostExtender::instance()->get_postx($post);
}
