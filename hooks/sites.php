<?php
/**
 * Registered sites list
 */

add_action( 'init', function() {

	register_post_type( 'registered-site', [
		'label'             => __( 'Registered Sites', 'warifu' ),
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => 'edit.php?post_type=license',
		'show_in_nav_menus' => false,
		'show_in_admin_bar' => false,
		'supports'          => false,
	] );

} );

add_action( 'edit_form_after_title', function( WP_Post $post ) {
	if ( 'registered-site' == $post->post_type ) {
		printf( '<h1>#%d</h1>', $post->ID );
	}
} );

/**
 * Add meta boxes
 */
add_action( 'add_meta_boxes', function( $post_type ) {
	if ( 'registered-site' == $post_type ) {
		add_meta_box( 'warifu-site-info', __( 'Site Info', 'warifu' ), function( $post ) {
			wp_nonce_field( 'warifu_site', '_warifu_site_nonce', false );
			warifu_template( 'admin-site-info', [
				'post' => $post,
			] );
		}, $post_type, 'normal', 'high' );
	}
} );

/**
 * Save license and url
 */
add_action( 'save_post', function( $post_id, WP_Post $post ) {
	if ( 'registered-site' != $post->post_type ) {
		return;
	}
	if ( ! isset( $_POST['_warifu_site_nonce'] ) || ! wp_verify_nonce( $_POST['_warifu_site_nonce'], 'warifu_site' ) ) {
		return;
	}
	// Save URL and license
	update_post_meta( $post_id, '_warifu_url', $_POST['warifu_url'] );
	update_post_meta( $post_id, '_warifu_license', $_POST['warifu_license'] );
	global $wpdb;
	$wpdb->update( $wpdb->posts, [
		'post_parent' => $_POST['warifu_product'],
	], [ 'ID' => $post_id ], ['%d'], ['%d'] );
	clean_post_cache( $post );
}, 10, 2 );


/**
 * Check license sanity
 */
add_action( 'save_post', function( $post_id, WP_Post $post ) {
	if ( 'registered-site' != $post->post_type || 'publish' != $post->post_status ) {
		return;
	}
	if ( ! isset( $_POST['_warifu_site_nonce'] ) || ! wp_verify_nonce( $_POST['_warifu_site_nonce'], 'warifu_site' ) ) {
		return;
	}
	$info = warifu_get_license_info( warifu_guid( $post->post_parent ), warifu_get_license( $post ) );
	if ( is_wp_error( $info ) && 'publish' == $post->post_status ) {
		// This license is invalid.
		switch ( $info->get_error_code() ) {
			case 'invalid_response':
				// Cannot access Gumroad
				warifu_add_log( __( 'Tried to check validity of license, but bad response from Gumroad. So, status is left unchanged.', 'warifu' ), true, $post );
				break;
			case 'invalid_license':
				// License is invalid.
				warifu_add_log( __( 'Tried to validate this license from admin panel, but invalid. So change status.', 'warifu' ), true, $post );
				global $wpdb;
				$wpdb->update( $wpdb->posts, [
					'post_status' => 'private',
				], [ 'ID' => $post_id ], ['%s'], ['%d'] );
				clean_post_cache( $post );
				break;
		}
	}
}, 20, 2 );


/**
 * Add column
 */
add_filter( 'manage_registered-site_posts_columns', function( $column ) {
	$new_column = [];
	foreach ( $column as $key => $label ) {
		$new_column[ $key ] = $label;
		if ( 'title' == $key ) {
			$new_column['url'] = 'URL';
			$new_column['parent'] = __( 'Product', 'warifu' );
			$new_column['owner'] = __( 'Owner', 'warifu' );
			$new_column['status'] = __( 'Status', 'warifu' );
			$new_column['comments'] = __( 'Log', 'warifu' );
		}
	}
	return $new_column;
} );

/**
 * Show license info
 */
add_action( 'manage_registered-site_posts_custom_column', function ( $column, $post_id ) {
	$post = get_post( $post_id );
	switch ( $column ) {
		case 'url':
			$url = get_post_meta( $post_id, '_warifu_url', true );
			$html = $url ? sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), esc_html( $url ) ) : '---';
			echo $html;
			break;
		case 'parent':
			printf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( "edit.php?post_type=license&page=registered-sites&parent=$post->post_parent" ) ),
				warifu_guid( $post->post_parent )
			);
			break;
		case 'owner':
			if ( $owner = warifu_license_owner( warifu_guid( $post ), warifu_get_license( $post ) ) ) {
				printf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( "edit.php?post_type=license&page=registered-sites&author={$owner->ID}" ) ),
					esc_html( $owner->display_name )
				);
			} else {
				echo '---';
			}
			break;
		case 'status':
			echo warifu_status_label( $post_id );
			break;
		default:
			// Do nothing.
			break;
	}
}, 10, 2 );
