<?php
// Template file for license register form
/** @var string $title */
?>
<div class="warifu-license-wrapper">

	<?php if ( $title ) : ?>
	<h3 class="warifu-license-title"><?php _e( 'Support', 'warifu' ) ?></h3>
	<?php endif; ?>

	<p class="warifu-license-p">
		<?php printf( __( 'You have to <a rel="nofollow" href="%s">log in</a> to get supported.', 'warifu' ), wp_login_url( get_permalink() ) ) ?>
	</p>

</div><!-- //.warifu-license-key -->
