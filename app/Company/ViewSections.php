<?php

class Company_ViewSections extends ViewSections {
	
	function reserves_table( $reserves ){
		
		self::s('<div class="row"><div class="col-sm-12">');
		self::s('<h2>Reserves <small>and ' . add_tooltip('GtCO2', 'Gigatons (1 Billion tons) of Carbon Dioxide') . '</small></h2>');
		
		$table = self::createTable()
			->addClasses(array('table', 'table-bordered', 'table-striped', 'table-hover', 'table-sortable'));
		
		$reserveHelper =& get_helper('reserve');
		$reserve_types = get_unique_reserve_types($reserves);
		
		$columns = array_merge(array("Year"), $reserve_types, array("GtCO2", "mmboe", "MtCO2/mmboe"));
		
		$table->thead( $columns );
		
		foreach($reserves as $reservesObj){
			
			$row = $table->row();
			
			$row->cell( "<b>{$reservesObj->year}</b>" );
			
			foreach($reservesObj->reserves as $type => $reserve){
				$row->cell( 
					"<small rel='tooltip' title='GtCO2' class='text-muted pull-right'>"
					. "{$reserve->gt_co2}</small>"
					. "{$reserve->quantity} {$reserve->unit} "
					. $reserve->energy_equivalent
				);
			}
			
			$row->cell( number_format($reservesObj->gt_co2, 3) );
			$row->cell( number_format($reservesObj->get_mmboe(), 3) );
			$row->cell( number_format($reservesObj->get_co2_per_mmboe(), 3) );
		}
		
		self::s( $table->__toString() . '</div></div>');
		
		self::output();
	}
	
	function info_sidebar( $args ){
		
		extract( $args );
		
	}
		
	
}