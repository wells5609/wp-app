<?php

$company_id = wp_filter_kses( $_GET['company_post_id'] );

?>

<form class="form-horizontal" role="form" id="add-reserves" action="<?php the_permalink(); ?>" method="post">
	
	<div class="form-group">
	<?php
	
		$year = new HTML_Input('select');
		$year->set('name', 'year')
			->set('label', 'Year')
			->setOptions(array('2013'=>'2013', '2012'=>'2012', '2011'=>'2011', '2010'=>'2010'))
			->wrap('div', 'class="col-sm-4"')
			->set('label_attributes', 'class="col-sm-3"')
			->addClass('input-lg')
			->render('e');
		
	?>
	</div>
	
	<br>
	
	<div class="form-group">
	<?php 
	
		$oil = new HTML_Input('text');
		$oil->set('name', 'oil.crude')
			->set('label', 'Crude Oil')
			->set('placeholder', 'mmbbl')
			->set('label_attributes', 'class="col-sm-3"')
			->addClass('input-lg')
			->wrap('div', 'class="col-sm-3"')
			->render('e');
		
		$bitumen = new HTML_Input('text');
		$bitumen->set('name', 'oilbitumen')
			->set('label', 'Bitumen')
			->set('placeholder', 'mmbbl')
			->addClass('input-lg')
			->set('label_attributes', 'class="col-sm-2"')
			->wrap('div', 'class="col-sm-3"')
			->render('e');
		
	?>
	</div>
	
	<div class="form-group">
	<?php 
	
		$gas = new HTML_Input('text');
		$gas->set('name', 'gas')
			->set('label', 'Natural Gas')
			->set('placeholder', 'bcf')
			->addClass('input-lg')
			->set('label_attributes', 'class="col-sm-3"')
			->wrap('div', 'class="col-sm-3"')
			->render('e');
		
		$syn = new HTML_Input('text');
		$syn->set('name', 'oil.synthetic')
			->set('label', 'Synthetic Oil')
			->set('placeholder', 'mmbbl')
			->addClass('input-lg')
			->set('label_attributes', 'class="col-sm-2"')
			->wrap('div', 'class="col-sm-3"')
			->render('e');
		
	?>	
	</div>
	
	<div class="form-group">
	<?php 
	
		$ngls = new HTML_Input('text');
		$ngls->set('name', 'ngl')
			->set('label', 'Natural Gas Liquids (NGLs)')
			->set('placeholder', 'mmbbl')
			->addClass('input-lg')
			->set('label_attributes', 'class="col-sm-3"')
			->wrap('div', 'class="col-sm-3"')
			->render('e');
		
		
	?>	
	
	<br>
	
	</div>
	
	<div class="form-group">
		<div class="col-sm-3 col-sm-push-3">
		<div class="checkbox">
		<label>
			<input data-toggle="collapse" data-target="#coal-panel" type="checkbox"> <b>Includes Coal</b>
		</label>
		</div>
		</div>
	</div>
	
	<div class="collapse row" id="coal-panel">
		
		<div class="form-group">
		<?php 
		
			$bituminous = new HTML_Input('text');
			$bituminous->set('name', 'bituminous')
				->set('label', 'Bituminous Coal')
				->set('placeholder', 'Mt (megatons)')
				->wrap('div', 'class="col-sm-3"')
				->addClass('input-lg')
				->set('label_attributes', 'class="col-sm-3"')
				->render('e');
			
			$subbituminous = new HTML_Input('text');
			$subbituminous->set('name', 'subbituminous')
				->set('label', 'Sub-bituminous Coal')
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
			$anthracite->set('name', 'anthracite')
				->set('label', 'Anthracite Coal')
				->set('placeholder', 'Mt (megatons)')
				->set('label_attributes', 'class="col-sm-3"')
				->wrap('div', 'class="col-sm-3"')
				->addClass('input-lg')
				->render('e');
			
			$lignite = new HTML_Input('text');
			$lignite->set('name', 'lignite')
				->set('label', 'Lignite Coal')
				->set('placeholder', 'Mt (megatons)')
				->set('label_attributes', 'class="col-sm-2"')
				->wrap('div', 'class="col-sm-3"')
				->addClass('input-lg')
				->render('e');
			
		?>
		</div>
		
	</div>
	
	<hr>
	
	<div class="form-group<?php if (isset($errors['human'])) echo ' has-error' ?>">
		
		<label class="col-sm-4 control-label" for="human_check">Human Check</label>
		
		<div class=" col-sm-4">
		
			<input type="text" name="human_check" id="human_check" class="required form-control input-lg" value="" />
		
			<span class="help-inline"><?php _e('A three-letter antonym for "good".', 'bootstrapped'); ?></span>
		
			<?php if (isset($errors['human'])) { ?>
				<span class="help-block"><?php echo $handler->get_error_message('human'); ?></span>
			<?php } ?>
		
		</div>
	</div>
	
	<div class="form-actions col-sm-push-4 col-sm-4">
		<button class="btn btn-lg btn-block btn-primary" id="submit" type="submit">Add Reserve &raquo;</button>
	</div>
	
	<?php
		$company = new HTML_Input('hidden');
		$company->set('value', $company_id)
			->render('e');
	?>
	
	<input type="hidden" name="nonce" id="nonce" class="hidden" value="<?php echo wp_create_nonce('add-company-nonce'); ?>" />
	
	<input type="hidden" name="submitted" id="submitted" class="hidden" value="true" />
	
	<input type="text" name="honey" id="honey" class="hidden" style="display:none;" value="" />
	
</form>