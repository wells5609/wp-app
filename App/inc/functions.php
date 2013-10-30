<?php

// Meta

function get_meta_model( $type ){
	
	return MetaManager::instance()->get_model($type);	
}

function get_meta_object( $type, $id, $meta_key = null, $output = OBJECT ){
	
	if ( $type instanceof Meta_Model ){
		return $type->get_object($id, $meta_key, $output);	
	}
	
	return MetaManager::instance()
		->get_model($type)
			->get_object($id, $meta_key, $output);
}


function get_xmeta_value( $type, $id_column, $meta_key = null ){
	
	if ( $type instanceof Meta_Model ){
		return $type->get_value($id_column, $meta_key);	
	}
	
	return MetaManager::instance()
		->get_model($type)
			->get_value($id_column, $meta_key);
}


// Postx

function get_postx_model( $type ){
	
	return PostxManager::instance()->get_model($type);
}

function get_postx( $post = null ){
	
	return PostxManager::instance()->get_postx($post);
}
