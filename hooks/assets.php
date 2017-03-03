<?php

/**
 * Register Helper script
 */
add_action( 'init', function() {
	// gumdroad embed script
	wp_register_script( 'gumroad-embed', 'https://gumroad.com/js/gumroad-embed.js', [], null, true );
	wp_register_script( 'gumroad-button', 'https://gumroad.com/js/gumroad.js', [], null, true );
	wp_register_script( 'warifu-helper', warifu_asset_path( 'assets/js/warifu-helper.js' ), [ 'jquery' ], WARIFU_VERSION, true );
	// Warifu admin
	wp_register_style( 'warifu-admin', warifu_asset_path( 'assets/css/warifu-admin.css' ), [], WARIFU_VERSION );
} );

add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style( 'warifu-admin' );
} );
