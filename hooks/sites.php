<?php
/**
 * Registered sites list
 */

/**
 * Add menu page
 */
add_action( 'admin_menu', function() {
	$title = __( 'Registered Sites', 'warifu' );
	add_submenu_page( 'edit.php?post_type=license', $title, $title, 'edit_posts', 'registered-sites', function() {
		warifu_template( 'admin-sites' );
	} );
} );

/**
 * Add site
 */
add_action( 'admin_init', function() {
	if ( ! isset( $_POST['_warifu_add_site'] ) || ! wp_verify_nonce( $_POST['_warifu_add_site'], 'warifu_add_site' ) ) {
		return;
	}
	try {
		// Check license exists.
		$post = get_post( $_POST['warifu_parent'] );
		if ( ! $post || ( 'license' !== $post->post_type ) ) {
			throw new \Exception( __( 'No product found.', 'warifu' ), 404 );
		}
		// Check permission.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			throw new \Exception( __( 'You have no permission.', 'warifu' ), 401 );
		}
		// Check status
		switch ( $_POST['warifu_status'] ) {
			case 'publish':
			case 'draft':
			case 'private':
				$status = $_POST['warifu_status'];
				break;
			default:
				throw new \Exception( __( 'Unregistered status specified.', 'warifu' ), 400 );
				break;
		}
		// Try to add license.
		$license = $_POST['warifu_license_key'];
		$response = warifu_register_site( $license, $_POST['warifu_url'], $status, $post );
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message(), 500 );
		}
		wp_redirect( admin_url( 'edit.php?post_type=license&page=registered-sites&message=updated' ) );
		exit;
	} catch ( \Exception $e ) {
		wp_die( $e->getMessage(), get_status_header_desc( $e->getCode() ), [
			'response'  => $e->getCode(),
			'back_link' => true,
		] );
	}
} );
