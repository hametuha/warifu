<?php
/**
Plugin Name: Warifu
Plugin URI: https://gianism.info/add-on/warifu/
Description: A WordPress plugin which handles User have gumroad license key.
Author: Hametuha INC.
Version: 1.0.6
PHP Version: 5.5.0
Author URI: https://gianism.info/
Text Domain: warifu
*/


defined( 'ABSPATH' ) or die();


add_action( 'plugins_loaded', function() {

	$domain = load_plugin_textdomain( 'warifu', false, 'warifu/languages' );

	$warifu_info = get_file_data( __FILE__, array(
		'version' => 'Version',
		'php'     => 'PHP Version',
	) );

	define( 'WARIFU_VERSION', $warifu_info['version'] );
	try {
		if ( ! version_compare( phpversion(), $warifu_info['php'], '>=' ) ) {
			add_action( 'admin_notices' );
			throw new Exception( sprintf( __( '[Warifu] PHP <code>%s</code> is required, but your version is <code>%s</code>. So this plugin is still in silence. Please contact server administrator.', 'warifu' ), $warifu_info['php'], phpversion() ) );
		}
		// Composer
		require dirname( __FILE__ ) . '/vendor/autoload.php';
		// Include Sekisyo
		include dirname( __FILE__ ) . '/sekisyo.php';
		// Load functions
		$dirs = array( 'functions', 'classes' );
		if ( apply_filters( 'warifu_is_sekisyo_passed', true ) ) {
			$dirs[] = 'hooks';
		}
		foreach ( $dirs as $dir_name ) {
			$dir = dirname( __FILE__ ) . '/' . $dir_name . '/';
			foreach ( scandir( $dir ) as $file ) {
				if ( preg_match( '#^[^.](.*)\.php$#u', $file ) ) {
					require $dir . $file;
				}
			}
		}
	} catch ( Exception $e ) {
		$error = sprintf( '<div class="error"><p>%s</p></div>', $e->getMessage() );
		add_action( 'admin_notices', function () use ( $error ) {
			echo $error;
		} );
	}
} );



// Flush rewrite rules on activation
register_activation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );
