<?php 
	
	$Admin = AppAdmin_InstallTables::instance();
	
	$tables = $Admin->get_tables();
	
	$all_tables = $tables['registered'];
	$installed_tables = $tables['installed'];
	
	//$action_message = $Admin->page_request($all_tables, $installed_tables);
	
	vardump( array_keys($Admin->get_schemas()), $all_tables, $installed_tables );
	
	$page_slug = 'app-tables';
	
	if ( $action_message ){
		echo '<div id="message" class="';
		if ( in_array(0, $action_message) ){
			echo 'error">';
		}
		else
			echo 'updated">';
		
		foreach($action_message as $table => $success){
			echo '<p><b>Table ';
			if ( true === $success )
				echo $table . ' was installed!';
			else
				echo $table . ' could not be installed.';
			echo '</b></p>';
		}
		echo '</div>';
	}
	
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>App Tables</h2>
	<div class="clear"></div>
	<form action="admin.php?page=<?php echo $page_slug; ?>" method="post">
		<?php wp_nonce_field('update-options'); ?>
		
		<table id="all-plugins-table" class="widefat">
			<thead>
				<tr>
					<th class="manage-column" scope="col">Table</th>
					<th class="manage-column" scope="col">Installed</th>
					<th class="manage-column" scope="col">Fields</th>
					<th class="manage-column" scope="col">Keys</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="manage-column" scope="col">Table</th>
					<th class="manage-column" scope="col">Installed</th>
					<th class="manage-column" scope="col">Fields</th>
					<th class="manage-column" scope="col">Keys</th>
				</tr>
			</tfoot>
			<tbody class="plugins">
			<?php
			
			foreach ($all_tables as $basename => $fullname) :
			  
				$active = in_array($fullname, $installed_tables);
				
				$schema = $Admin->get_schema_from_table($basename);
							
			  ?>
				<tr class="<?php echo ($active ? 'active' : 'inactive'); ?>">
					<td class="plugin-title">
						<strong><?php echo $fullname; ?></strong>
						<div class="row-actions-visible">
							<?php if ( $active ) {
								$url = "admin.php?page={$page_slug}&amp;action=drop&amp;table={$fullname}";
								echo '<a href="' . esc_url(wp_nonce_url($url, 'update-options')) . '" title="' . esc_attr('Drop this table') . '" class="edit">' . __('Drop') . '</a>';
							}
						else {
							$url = "admin.php?page={$page_slug}&amp;action=install&amp;table={$fullname}";
							echo '<a href="' . esc_url(wp_nonce_url($url, 'update-options')) . '" title="' . esc_attr('Install this table') . '" class="edit">' . __('Install') . '</a>';
						} ?>
						</div>
					</td>
					<td>
						<?php echo '<b>' , $active? 'Yes' : 'No' , '</b>'; ?>
					</td>
					<td>
						<?php 
							foreach($schema->field_names as $name => $settings){
								echo '<code><b>' . $name . '</b></code>: ';	
								echo '<code>' . $settings . '</code><br>';
							}
						?>
					</td>
					<td>
						<?php 
						echo '<b>Primary:</b> <code>' . $schema->primary_key . '</code><br>';
						if ( !empty($schema->unique_keys) ){
							echo '<b>Unique:</b> <br>';
							foreach($schema->unique_keys as $key){
								echo '<code>' . $key . '</code><br>';
							}
						}
						if ( !empty($schema->keys) ){
							echo '<b>Keys:</b> <br>';
							foreach($schema->keys as $key){
								echo '<code>' . $key . '</code><br>';
							}
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action">
					<option selected="selected" value="-1">Bulk Actions</option>
					<option value="activate">Activate</option>
					<option value="deactivate">Deactivate</option>
				</select>
				<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="Apply">
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</form>
</div>