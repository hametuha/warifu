<?php
/**
Plugin Name: Warifu
Plugin URI: https://gianism.info/add-on/warifu/
Description: A WordPress plugin which handles User have gumroad license key.
Author: Hametuha INC.
Version: 1.0.1
PHP Version: 5.5.0
Author URI: https://gianism.info/
Text Domain: warifu
*/

$warifu_info = get_file_data( __FILE__ , array(
	'version' => 'Version',
	'php'     => 'PHP Version',
) );
define( 'WARIFU_VERSION', $warifu_info['version'] );

load_plugin_textdomain( 'warifu', false, 'warifu/languages' );

try {
	if ( ! version_compare( phpversion(), $warifu_info['php'], '>=' ) ) {
		throw new Exception( sprintf( __( '[Warifu] PHP <code>%s</code> is required, but your version is <code>%s</code>. So this plugin is still in silence. Please contact server administrator.', 'warifu' ), $warifu_info['php'], phpversion() ) );
	}
	// Composer
	require __DIR__.'/vendor/autoload.php';
	// Load functions
	foreach ( array( 'hooks', 'functions', 'classes' ) as $dir_name ) {
		$dir = __DIR__.'/'.$dir_name.'/';
		foreach ( scandir( $dir ) as $file ) {
			if ( preg_match( '#^[^.](.*)\.php$#u', $file ) ) {
				require $dir.$file;
			}
		}
	}
} catch ( Exception $e ) {
	$error = sprintf( '<div class="error"><p>%s</p></div>', $e->getMessage() );
	add_action( 'admin_notices', create_function( '', sprintf( 'echo \'%s\';', str_replace( '\'', '\\\'', $error ) ) ) );
}

// Flush rewrite rules on activation
register_activation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );