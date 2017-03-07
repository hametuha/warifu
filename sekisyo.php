<?php
/**
 * Sekisyo bootstrap
 */

\Hametuha\Sekisyo\GateKeeper::register(
	'biyqH',
	__DIR__ . '/warifu.php',
	__( 'Warifu Single License', 'warifu' ),
	__( 'You can install warifu for this site', 'warifu' ),
	'https://gianism.info/wp-json/warifu/v1/license/biyqH/',
	12
);

/**
 * Show error message
 */
add_action( 'admin_notices', function() {
	if ( current_user_can( 'activate_plugins' ) && ! \Hametuha\Sekisyo\GateKeeper::is_valid( [ 'biyqH' ] ) ) {
		printf(
			'<div class="error"><p>%s</p></div>',
			wp_kses( sprintf(
				__( 'Mmm... Warifu license is invalid. Please go to <a href="%s">setting page</a> and enter license. If you don\'t have one, grab license at <a href="%s" target="_blank">gianism.info</a>! ', 'warifu' ),
				admin_url( 'plugins.php?page=sekisyo' ),
				'https://gianism.info/add-on/warifu/'
			), [ 'a' => [ 'href' => true, 'target' => true ] ] )
		);
		if ( WP_DEBUG ) {
			printf(
				'<div class="updated"><p>%s</p></div>',
				__( 'You don\'t have license key, but Warifu works because of debug mode!', 'warifu' )
			);
		}
	}
} );

/**
 * Check if license is valid.
 */
add_filter( 'warifu_is_sekisyo_passed', function( $passed ) {
	if ( WP_DEBUG ) {
		// This is DEBUG mode, O.K.
		return true;
	}
	return \Hametuha\Sekisyo\GateKeeper::is_valid( [ 'biyqH' ] );
} );
