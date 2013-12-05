<?php

if ( ! is_user_logged_in() ) :
	echo '<div class="alert alert-warning alert-block"><h4>Woah there</h4>You must be <a href="' . esc_url(wp_login_url()) . '" title="Login">logged in</a> to view this page.</div>';	
else :

$company_id = wp_filter_kses( $_REQUEST['company_id'] );

?>

<div id="messages"></div>

<form class="form-horizontal" role="form" id="add-reserves" action="">
	
	<?php
		$grp1 = new HTML_Element('div');
		$grp1->addClass('form-group');
	
		$year = new HTML_Input('select');
		$year->set('name', 'year')
			->set('label', 'Year')
			->setOptions(array('2013'=>'2013', '2012'=>'2012', '2011'=>'2011', '2010'=>'2010'))
			->wrap('div', 'class="col-sm-4"')
			->set('label_attributes', 'class="col-sm-3"')
			->addClass('input-lg');
		
		$grp1->addContent($year)
			->render('e');
		
	?>
	
	<br>
	
	<?php 
		
		$grp2 = new HTML_Element('div');
		$grp2->addClass('form-group');
		
		
		$oil = new HTML_Input('text');
		$oil->set('name', 'crude')
			->set('label', 'Crude Oil')
			->set('placeholder', 'mmbbl')
			->set('label_attributes', 'class="col-sm-3"')
			->addClass('input-lg')
			->wrap('div', 'class="col-sm-3"');
		
		$bitumen = new HTML_Input('text');
		$bitumen->set('name', 'bitumen')
			->set('label', 'Bitumen')
			->set('placeholder', 'mmbbl')
			->addClass('input-lg')
			->set('label_attributes', 'class="col-sm-2"')
			->wrap('div', 'class="col-sm-3"');
		
		$grp2->addContent($oil)
			->addContent($bitumen)
			->render('e');
		
		
		$grp3 = new HTML_Element('div');
		$grp3->addClass('form-group');
	
	
		$gas = new HTML_Input('text');
		$gas->set('name', 'gas')
			->set('label', 'Natural Gas')
			->set('placeholder', 'bcf')
			->addClass('input-lg')
			->set('label_attributes', 'class="col-sm-3"')
			->wrap('div', 'class="col-sm-3"');
		
		$syn = new HTML_Input('text');
		$syn->set('name', 'synthetic')
			->set('label', 'Synthetic Oil')
			->set('placeholder', 'mmbbl')
			->addClass('input-lg')
			->set('label_attributes', 'class="col-sm-2"')
			->wrap('div', 'class="col-sm-3"');
		
		$grp3->addContent($gas)
			->addContent($syn)
			->render('e');
			
		
		$grp4 = new HTML_Element('div');
		$grp4->addClass('form-group');
		
		
		$ngls = new HTML_Input('text');
		$ngls->set('name', 'ngl')
			->set('label', 'Natural Gas Liquids (NGLs)')
			->set('placeholder', 'mmbbl')
			->addClass('input-lg')
			->set('label_attributes', 'class="col-sm-3"')
			->wrap('div', 'class="col-sm-3"');
		
		$coal_label = new HTML_Element('button');
		$coal_label->setContent('Add Coal')
			->setAttributes('type="button" class="btn btn-lg btn-default btn-block" data-toggle="collapse" data-target="#coal-panel"')
			->wrap('div', 'class="col-sm-3 col-sm-push-2"');
	
		
		$grp4->addContent($ngls)
			->addContent($coal_label)
			->render('e');	
	?>	
	
	<br>
	
	<div class="row collapse" id="coal-panel">
		<div class="well well-lg">
		<div class="form-group">
		<?php 
		
			$bituminous = new HTML_Input('text');
			$bituminous->set('name', 'coal_bituminous')
				->set('label', 'Bituminous')
				->set('placeholder', 'Mt (megatons)')
				->wrap('div', 'class="col-sm-3"')
				->addClass('input-lg')
				->set('label_attributes', 'class="col-sm-3"')
				->render('e');
			
			$subbituminous = new HTML_Input('text');
			$subbituminous->set('name', 'coal_subbituminous')
				->set('label', 'Sub-Bituminous')
				->set('placeholder', 'Mt (megatons)')
				->set('label_attributes', 'class="col-sm-2"')
				->wrap('div', 'class="col-sm-3"')
				->addClass('input-lg')
				->render('e');
			
		?>
		</div>
		<div class="form-group">
		<?php 
		
			$anthracite = new HTML_Input('text');
			$anthracite->set('name', 'coal_anthracite')
				->set('label', 'Anthracite')
				->set('placeholder', 'Mt (megatons)')
				->set('label_attributes', 'class="col-sm-3"')
				->wrap('div', 'class="col-sm-3"')
				->addClass('input-lg')
				->render('e');
			
			$lignite = new HTML_Input('text');
			$lignite->set('name', 'coal_lignite')
				->set('label', 'Lignite')
				->set('placeholder', 'Mt (megatons)')
				->set('label_attributes', 'class="col-sm-2"')
				->wrap('div', 'class="col-sm-3"')
				->addClass('input-lg')
				->render('e');
			
		?>
		</div>
		</div>
	</div>
	
	<hr>
	
	<?php
		
		$grp7 = new HTML_Element('div');
		$grp7->addClass('form-group');
	
		$human = new HTML_Input('text');
		$human->set('name', 'human_check')
			->addClass('input-lg')
			->set('label', 'Human Check')
			->set('label_attributes', 'class="col-sm-4"')
			->set('help_text', 'Three-letter antonym of "good"')
			->wrap('div', 'class="col-sm-4"');
		
		$grp7->addContent($human)
			->render('e');
			
	?>
	
	<div class="form-actions col-sm-push-4 col-sm-4">
		<button class="btn btn-lg btn-block btn-primary ajax-request-form-submit" type="button" data-action="add_company_reserves" 
		data-method="POST" data-q="" data-nonce="<?php echo wp_create_nonce('add-reserves-nonce'); ?>">Add Reserve &raquo;</button>
	</div>
	
	<input type="hidden" name="company_id" id="company_id" class="hidden" value="<?php echo $company_id ?>" />
	
	<input type="text" name="honey" id="honey" class="hidden" style="display:none;" value="" />
	
</form>

<?php 

endif; // is_user_logged_in()