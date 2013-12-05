<?php

class AddReservesHandler extends FormHandler {
	
	protected $validations = array(
		'validate_honey',
		'validate_human',
		'validate_nonce',
		'validate_year',
	);
	
	protected $processes = array(
		'insert_reserve',
		'update_company_reserves',
		'update_company',
	);
	
	protected $on_complete = array();
	
	protected $NONCE_NAME = 'add-reserves-nonce';
	
	public $reserve_types = array(
		'crude', 'bitumen', 'synthetic', 'ngl', 'gas', 'coal',
		'coal_bituminous', 'coal_subbituminous', 'coal_anthracite', 'coal_lignite',
	);
	
	public $reserves;
	
	public $includes_coal = false;
	
	public $total_co2;
	
	public $total_mmboe;
	
		
	function init(){
		
		if ( !isset($this->POST['company_id']) ){
			$this->errors['year'] = 'Year already exists.';
			return;
		}
		
		$this->post_id = $this->POST['company_id'];
		
		$this->model =& get_model('reserve');
		
		$years =& $this->model->get_results( "SELECT year FROM {$this->model->table} WHERE post_id = {$this->post_id}" );
		
		$this->existing_years = wp_list_pluck( $years, 'year');
		
	}
	
	function validate_year(){
		if ( in_array($this->POST['year'], $this->existing_years) ){
			$this->errors['year'] = 'Year already exists.';	
		}
	}
	
	function pre_insert_reserve(){
		
		$reserves = array();
		
		$co2 = $mmboe = 0;
		
		foreach($this->reserve_types as $type){
			
			if ( isset($this->POST[$type]) && !empty($this->POST[$type]) ){
				
				if ( strpos($type, '_') !== false )
					$type = str_replace('_', '.', $type);
				
				if ( str_startswith($type, 'coal') )
					$this->includes_coal = true;
				
				$reserves[ $type ] = array(
					'post_id' 			=> (int) $this->post_id,
					'year' 				=> (int) $this->POST['year'],
					'type'	 			=> $type,
					'unit'				=> ReserveHelper::get_unit($type),
					'classification'	=> 'P1',
					'quantity' 			=> number_format($this->POST[$type], 4, '.', ''),
					'mmboe'				=> ReserveHelper::convert_to_mmboe( $type, $this->POST[$type] ),
					'gt_co2'			=> ReserveHelper::convert_to_co2( $type, $this->POST[$type] )
				);
				
				$co2 += $reserves[ $type ]['gt_co2']; // add co2 for Company_Reserves
				$mmboe += $reserves[ $type ]['mmboe']; // add co2 for Company_Reserves
				
			}	
		}
		
		$this->reserves = $reserves;
		$this->total_co2 = $co2;
		$this->total_mmboe = $mmboe;
	}
	
	
	function insert_reserve(){
		foreach($this->reserves as $arg => $reserve){
			$this->model->insert( $reserve );
		}
	}
	
		
	function update_company_reserves(){
		
		$year = $this->POST['year'];
		
		$rid_list = $this->model->get_results( "SELECT rid from {$this->model->table} WHERE post_id = {$this->post_id} AND year = $year" );
		
		$rid_list_string = implode(',', wp_list_pluck($rid_list, 'rid'));
	
		get_model('Company_Reserves')
		->insert( array(
			'post_id' => $this->post_id, 
			'year' => $year, 
			'reserve_ids' => $rid_list_string, 
			'includes_coal' => $this->includes_coal ? 1 : 0,
			'mmboe' => $this->total_mmboe,
			'gt_co2' => $this->total_co2,
		) );
		
		$this->msgs['reserves'] = 'Added ' . $this->POST['year'] . ' reserves for ' . get_the_title($this->post_id) . '';
	}
	
	function update_company(){
		
		$model =& get_meta_model('company');
		
		$has_reserves = $model->query_by_multiple( array('post_id' => $this->post_id, 'meta_key' => 'has_reserves') );
		
		if ( ! $has_reserves )
			$model->insert( array(
				'post_id' => $this->post_id, 
				'meta_key' => 'has_reserves', 
				'meta_value' => 1), 
			array('%d', '%s', '%s') 
		);
		
	}
	
	
}