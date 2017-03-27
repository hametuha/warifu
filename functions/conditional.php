<?php
/**
 * Conditional functions
 */

/**
 * Detect if current post type supports
 *
 * @param string $post_type
 *
 * @return bool
 */
function warifu_supported( $post_type ) {
	$post_types = warifu_post_types();
	return false !== array_search( $post_type, $post_types );
}

/**
 * Get post types which supports license
 *
 * @return array
 */
function warifu_post_types() {
	return (array) get_option( 'warifu_support_post_type', [] );
}

/**
 * Test Gumroad License API response.
 *
 * @param object|WP_Error $response
 *
 * @return bool
 */
function warifu_test_license( $response ) {
	if ( is_wp_error( $response ) ) {
		return false;
	}
	if ( isset( $response->purchase->refunded ) && $response->purchase->refunded ) {
		return false;
	}
	if ( isset( $response->purchase->subscription_cancelled_at ) && $response->purchase->subscription_cancelled_at ) {
		return false;
	}
	return true;
}

/**
 * Get license information
 *
 * @param string $guid
 * @param string $license
 *
 * @return object|WP_Error
 */
function warifu_get_license_info( $guid, $license ) {
	$ch = curl_init( 'https://api.gumroad.com/v2/licenses/verify' );
	curl_setopt_array( $ch, [
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_POST           => true,
		CURLOPT_POSTFIELDS     => "product_permalink={$guid}&license_key={$license}",
	] );
	$result = curl_exec( $ch );
	curl_close( $ch );
	if ( ! $result ) {
		return new WP_Error( 'invalid_response', __( 'Sorry, but failed to get response from Gumroad.', 'warifu' ), [ 'status' => 400 ] );
	}
	// If return is mal-formed, this may be gumroads, error
	$json = json_decode( $result );
	if ( ! $json || ! isset( $json->success ) ) {
		return new WP_Error( 'invalid_response', __( 'Sorry, but failed to get response from Gumroad.', 'warifu' ), [ 'status' => 400 ] );
	}
	if ( $json->success ) {
		return $json;
	} else {
		return new WP_Error( 'invalid_license', __( 'Mmm... This license is invalid.', 'warifu' ), [ 'status' => 403 ] );
	}
}
