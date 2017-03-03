<?php

\Hametuha\Sekisyo\GateKeeper::register(
	'biyqH',
	dirname( dirname( __FILE__ ) ) . '/warifu.php',
	__( 'Warifu Single License', 'warifu' ),
	__( '', 'warifu' ),
	'https://gianism.info/wp-json/warifu/v1/license/biyqH/'
);

add_action( 'admin_notices', function() {
	if ( current_user_can( 'activate_plugins' ) && ! \Hametuha\Sekisyo\GateKeeper::is_valid( [ 'biyqH' ] ) ) {
		printf( '<div class="error"><p>%s</p></div>', esc_html__( 'Mmm... Warifu license is invalid...', 'warifu' ) );
	}
} );
