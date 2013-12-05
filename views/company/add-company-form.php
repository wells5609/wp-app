<?php
/** Add company form */

if ( ! is_user_logged_in() ) :
	echo '<div class="alert alert-warning alert-block"><h4>Woah there</h4>You must be <a href="' . esc_url(wp_login_url()) . '" title="Login">logged in</a> to view this page.</div>';	
else :

?>

<div id="messages"></div>

<form class="form-horizontal" role="form" id="create-new-post">
	
	<br>
	
	<?php
		$grp1 = new HTML_Element('div');
		$grp1->addClass('form-group');
	
		$ticker = new HTML_Input('text');
		$ticker->set('name', 'ticker')
			->addClass('input-lg')
			->set('label', 'Ticker')
			->set('label_attributes', 'class="col-sm-4"')
			->wrap('div', 'class="col-sm-4"');
		
		$grp1->addContent($ticker)
			->render('e');
		
		$grp2 = new HTML_Element('div');
		$grp2->addClass('form-group');
	
		$human = new HTML_Input('text');
		$human->set('name', 'human_check')
			->addClass('input-lg')
			->set('label', 'Human Check')
			->set('label_attributes', 'class="col-sm-4"')
			->set('help_text', 'Three-letter antonym of "good"')
			->wrap('div', 'class="col-sm-4"');
		
		$grp2->addContent($human)
			->render('e');
			
	?>
	
	<div class="form-actions col-sm-push-4 col-sm-4">
		<?php 
			echo ajax_html(array(
				'tag' => 'button',
				'class' => 'btn btn-lg btn-block btn-primary ajax-request-form-submit',
				'type' => 'button',
				'action' => 'add_company',
				'method' => 'POST',
				'q' => '',
				'nonce' => ajax_create_nonce('add-company-nonce'),
				'content' => 'Add Company &raquo;',
			));
		?>
	</div>
	
	<input type="text" name="honey" id="honey" class="hidden" style="display:none;" value="" />
	
</form>

<?php endif; // is_user_logged_in ?>
