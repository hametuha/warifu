<?php
/**
 * Customer related functions
 */

/**
 * Add meta boxes
 */
add_action( 'add_meta_boxes', function( $post_type ) {
	if ( 'customer' == $post_type ) {
		add_meta_box( 'warifu-user-info', __( 'User Info', 'warifu' ), function( $post ) {
			wp_nonce_field( 'warifu_customer', '_warifu_customer_nonce', false );
			warifu_template( 'admin-customer-info', [
				'post' => $post,
			] );
		}, $post_type, 'normal', 'high' );

	}
	if ( false !== array_search( $post_type, [ 'customer', 'registered-site' ] ) ) {
		add_meta_box( 'warifu-user-log', __( 'Log', 'warifu' ), function( $post ) {
			warifu_template( 'admin-customer-log', [
				'post' => $post,
			] );
		}, $post_type, 'advanced' );
	}
} );

/**
 * Show user name
 */
add_action( 'edit_form_after_title', function( $post ) {
	if ( 'customer' != $post->post_type ) {
		return;
	}
	$author = get_userdata( $post->post_author );
	printf( '<h2>#%d %s</h2>', $post->post_author, $author ? esc_html( $author->display_name ) : __( 'Undefined', 'warifu' ) );
} );

/**
 * Filter title
 */
add_filter( 'the_title', function( $title, $post_id ) {
	if ( 'customer' == get_post_type( $post_id ) ) {
		$title = sprintf( '# %d', $post_id );
	}
	return $title;
}, 10, 2 );

/**
 * Save data
 */
add_action( 'save_post', function( $post_id, $post ) {
	if ( 'customer' != $post->post_type ) {
		return;
	}
	if ( ! isset( $_POST['_warifu_customer_nonce'] ) || ! wp_verify_nonce( $_POST['_warifu_customer_nonce'], 'warifu_customer' ) ) {
		return;
	}
	// O.K. Save license.
	$old_parent = $post->post_parent;
	$old_license = get_post_meta( $post_id, '_warifu_license', true );
	// New data
	$new_parent  = $_POST['warifu_product'];
	$parent = get_post( $new_parent );
	$new_license = $_POST['warifu_license'];
	// Check validity
	if ( ( $old_parent == $new_parent ) && ( $old_license == $new_license ) ) {
		// Nothing changes.
		return;
	}
	// Check parent.
	if ( $old_parent != $new_parent ) {
		if ( ! $parent || 'license' != $parent->post_type ) {
			// Parent is wrong.
			warifu_add_log( __( 'Wrong parameter is specified. Producthas invalid post type.', 'warifu' ), true, $post );
			return;
		} else {
			// O.K. Save new parent.
			global $wpdb;
			$wpdb->update( $wpdb->posts, [ 'post_parent' => $parent->ID ], [ 'ID' => $post->ID ], [ '%d' ], [ '%d' ] );
		}
	}
	// Check license.
	if ( $new_parent != $old_license ) {
		// Check and save license.
		$guid = warifu_guid( $parent );
		$result = warifu_get_license_info( $guid, $new_license );
		if ( is_wp_error( $result ) ) {
			warifu_add_log( $result->get_error_message() . " : {$new_license}", true, $post );
			return;
		} else {
			update_post_meta( $post->ID, '_warifu_license', $new_license );
			warifu_add_log( __( 'New license has been saved.', 'warifu' ), false, $post );
		}
	}
	clean_post_cache( $post );
}, 10, 2 );

// Custom column
add_filter( 'manage_customer_posts_columns', function( $column ) {
	$new_column = [];
	foreach ( $column as $key => $val ) {
		if ( 'title' == $key ) {
			$new_column = array_merge( $new_column, [
				'title'   => $val,
				'owner'   => __( 'Owner', 'warifu' ),
				'product' => __( 'Product', 'warifu' ),
				'status'  => __( 'Status', 'warifu' ),
				'comments' => __( 'Log', 'warifu' ),
			] );
		} else {
			$new_column[ $key ] = $val;
		}
	}
	return $new_column;
} );

// Show custom column.
add_action( 'manage_customer_posts_custom_column', function( $column, $post_id ) {
	$post = get_post( $post_id );
	switch ( $column ) {
		case 'owner':
			printf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'user-edit.php?user_id=' . $post->post_author ) ),
				get_the_author_meta( 'display_name', $post->post_author )
			);
			break;
		case 'product':
			printf(
				'<a href="%s">%s</a>',
				get_edit_post_link( $post->post_parent ),
				get_the_title( $post->post_parent )
			);
			break;
		case 'status':
			switch ( $post->post_status ) {
				case 'publish':
					$label = __( 'Valid', 'warifu' );
					$color = 'green';
					$icon  = 'yes';
					break;
				case 'private':
					$label = __( 'Invalid', 'warifu' );
					$color = 'red';
					$icon  = 'no';
					break;
				default:
					$label = __( 'Unknown', 'warifu' );
					$color = 'lightgrey';
					$icon  = 'editor-help';
					break;
			}
			printf(
				'<span style="color: %s"><span class="dashicons dashicons-%s"></span> %s</span>',
				esc_attr( $color ),
				esc_attr( $icon ),
				esc_html( $label )
			);
			break;
		default:
			// Do nothing.
			break;
	}
}, 10, 2 );
