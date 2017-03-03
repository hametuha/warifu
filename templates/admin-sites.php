<?php
defined( 'ABSPATH' ) or die();
?>
<div class="wrap warifu-wrap warifu-wrap-sites">

	<h2><?php esc_html_e( 'Registered Sites', 'warifu' ) ?></h2>

	<?php
	$table = new WarifuSitesList();
	$table->search_box( __( 'Search', 'warifu' ), 's' );
	$table->prepare_items();
	$table->display();
	?>

	<hr />

	<h2><?php esc_html_e( 'Manually Register Site', 'warifu' ) ?></h2>
	<form action="<?php echo esc_attr( admin_url( 'edit.php?post_type=license&page=registered-sites' ) ) ?>" method="post">
		<?php wp_nonce_field( 'warifu_add_site', '_warifu_add_site' ) ?>

		<table class="form-table">

			<tr>
				<th valign="top">
					<label for="warifu_parent">
						<?php _e( 'Product GUID', 'warifu' ) ?>
					</label>
				</th>
				<td>
					<?php warifu_pull_down( 'warifu_parent' ) ?>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="warifu_license_key">
						<?php _e( 'License Key', 'warifu' ) ?>
					</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="warifu_license_key" id="warifu_license_key"
					       value="" />
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="warifu_url">
						<?php _e( 'Site URL', 'warifu' ) ?>
					</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="warifu_url" id="warifu_url"
					       value="" />
				</td>
			</tr>

			<tr>
				<th valign="top">
					<label for="warifu_status">
						<?php _e( 'Status', 'warifu' ) ?>
					</label>
				</th>
				<td>
					<select name="warifu_status" id="warifu_status">
						<option value="publish"><?php _e( 'Valid', 'warifu' ) ?></option>
						<option value="private"><?php _e( 'Invalid', 'warifu' ) ?></option>
						<option value="draft"><?php _e( 'Inactive', 'warifu' ) ?></option>
					</select>
				</td>
			</tr>

		</table>

		<?php submit_button( __( 'Register', 'warifu' ) ) ?>

	</form>

</div>
