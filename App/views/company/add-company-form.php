<?php
/** Add company form */

if ( ! is_user_logged_in() ) :
	echo '<div class="alert alert-warning alert-block"><h4>Woah there</h4>You must be <a href="' . esc_url(wp_login_url()) . '" title="Login">logged in</a> to view this page.</div>';	
else :

if ( isset($_POST['submitted']) ){
	
	$handler = new AddCompanyHandler($_POST);
	
	if ( $handler->is_error() ){
		$errors = $handler->get_errors();
		foreach($errors as $type => $msg){
			echo '<div class="alert alert-danger alert-block"><h4>Error</h4>' . $msg . '</div>';
		}
	}
		
	if ( $handler->success ){
		foreach($handler->msgs as $message){
			echo '<div class="alert alert-success"><button type="button" class="close" aria-hidden="true">&times;</button>' . $message . '</div>';	
		}
	}

}
else {
	$handler = new stdClass;
	$errors = array();
}

?>

<form class="form-horizontal<?php if (isset($errors['exists'])) echo ' has-error' ?>" role="form" id="create-new-post" action="<?php the_permalink(); ?>" method="post">
	
	<div class="form-group<?php if (isset($errors['ticker'])) echo ' has-error' ?>">
		
		<label class="col-sm-4 control-label" for="ticker">Ticker</label>
		
		<div class="col-sm-4">
		
			<input type="text" name="ticker" id="ticker" value="" class="required form-control">
			
			<?php if (isset($errors['ticker'])) { ?>
				<span class="help-block"><?php echo $handler->get_error_message('ticker'); ?></span>
			<?php } ?>
		
		</div>
	</div>
	
	<div class="form-group<?php if (isset($errors['human'])) echo ' has-error' ?>">
		
		<label class="col-sm-4 control-label" for="human_check">Human Check</label>
		
		<div class=" col-sm-4">
		
			<input type="text" name="human_check" id="human_check" class="required form-control" value="" />
		
			<span class="help-inline"><?php _e('A three-letter antonym for "good".', 'bootstrapped'); ?></span>
		
			<?php if (isset($errors['human'])) { ?>
				<span class="help-block"><?php echo $handler->get_error_message('human'); ?></span>
			<?php } ?>
		
		</div>
	</div>
	
	<div class="form-actions col-sm-push-4 col-sm-4">
		<button class="btn btn-large btn-block btn-success" id="submit" type="submit">Add Company &raquo;</button>
	</div>
	
	<input type="hidden" name="nonce" id="nonce" class="hidden" value="<?php echo wp_create_nonce('add-company-nonce'); ?>" />
	
	<input type="hidden" name="submitted" id="submitted" class="hidden" value="true" />
	
	<input type="text" name="honey" id="honey" class="hidden" style="display:none;" value="" />
	
</form>

<?php endif; // is_user_logged_in(); ?>
