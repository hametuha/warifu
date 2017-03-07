<?php
/** @var WP_Post $post */
defined( 'ABSPATH' ) or die();
?>
<table class="form-table">
	<tr>
		<th valign="top">
			<label><?php _e( 'Owner', 'warifu' ) ?></label>
		</th>
		<td>
			<?php if ( $owner = warifu_license_owner( warifu_guid( $post ), warifu_get_license( $post ) ) ) : ?>
				<a href="<?php echo esc_attr( admin_url( 'user-edit.php?user_id='.$owner->ID ) ) ?>">
					<?php echo esc_html( $owner->display_name ) ?>
				</a>
			<?php else : ?>
				<span style="color: lightslategrey;"><?php esc_html_e( 'Unknown', 'warifu' ) ?></span>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<th valign="top">
			<label for="warifu_url">URL</label>
		</th>
		<td>
			<input type="text" class="regular-text" id="warifu_url" name="warifu_url"
				value="<?php echo esc_attr( get_post_meta( $post->ID, '_warifu_url', true ) ) ?>" />
		</td>
	</tr>
	<tr>
		<th valign="top">
			<label for="warifu_product">
				<?php _e( 'Product', 'warifu' ) ?>
			</label>
		</th>
		<td>
			<?php warifu_pull_down( 'warifu_product', $post->post_parent ) ?>
		</td>
	</tr>
	<tr>
		<th valign="top">
			<label for="warifu_license">
				<?php _e( 'License Key', 'warifu' ) ?>
			</label>
		</th>
		<td>
			<input type="text" class="regular-text" name="warifu_license" id="warifu_license"
				   value="<?php echo esc_attr( get_post_meta( $post->ID, '_warifu_license', true ) ) ?>" />
		</td>
	</tr>
	<tr>
		<th valign="top">
			<?php _e( 'Status', 'warifu' ) ?>
		</th>
		<td>
			<?php echo warifu_status_label( $post ) ?>
		</td>
	</tr>
	<tr>
		<th valign="top">
			<?php _e( 'Registered', 'warifu' ) ?>
		</th>
		<td>
			<?php echo mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) , $post->post_date ) ?>
		</td>
	</tr>
</table>
