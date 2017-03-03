<?php
// Template file for license register form
/** @var string $title */
?>
<div class="warifu-license-wrapper"
     data-href="<?php echo esc_url( warifu_api_url( '/license/'. get_the_ID() ) ) ?>"
	 data-post-id="<?php the_ID() ?>">

	<?php if ( $title ) : ?>
	<h3 class="warifu-license-title"><?= esc_html( $title ) ?></h3>
	<?php endif; ?>

	<p class="warifu-license-p">
		<label for=""><?php _e( 'Your License Key', 'warifu' ) ?></label>
		<input type="text" class="warifu-license-input" value="<?php echo esc_attr( warifu_user_key( get_current_user_id(), get_the_ID() ) ) ?>"
			placeholder="<?php printf( esc_attr__( 'Enter license key for %s', 'gumsp' ), get_the_title() ) ?>"/>
	</p>

	<p class="warifu-license-p">
		<span class="warifu-license-span">
			<?php printf( __( 'You can manage your license key at <a href="%s">license manager</a>.', 'warifu' ), warifu_license_center_url() ) ?>
		</span>
	</p>

	<p class="warifu-license-p">
		<button data-warifu="submit" class="button warifu-license-btn"><?php _e( 'Save License', 'warifu' ) ?></button>
	</p>

</div><!-- //.warifu-license-key -->
