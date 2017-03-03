<?php
/** @var WP_Post $post */
defined( 'ABSPATH' ) or die();
?>
<table class="form-table">
	<tr>
		<th valign="top">
			<label><?php _e( 'User Name', 'warifu' ) ?></label>
		</th>
		<td>
			<a href="<?php echo esc_attr( admin_url( 'user-edit.php?user_id='.$post->post_author ) ) ?>">
				<?php echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) ?>
			</a>
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
</table>
