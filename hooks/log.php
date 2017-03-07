<?php
/**
 * Log related hooks
 *
 * @package warifu
 * @since 1.0.0
 */

/**
 * Avoid log included
 *
 */
add_action( 'pre_get_comments', function ( WP_Comment_Query &$wp_comment_query ) {
	if ( ! $wp_comment_query->query_vars['type'] ) {
		// Exclude log
		$excluded = array_filter( (array) $wp_comment_query->query_vars['type__not_in'] );
		$excluded[] = 'warifu_log';
		$wp_comment_query->query_vars['type__not_in'] = $excluded;
	}
} );

/**
 * Add meta boxes
 */
add_action( 'add_meta_boxes', function( $post_type ) {
	if ( false !== array_search( $post_type, [ 'customer', 'registered-site' ] ) ) {
		add_meta_box( 'warifu-user-log', __( 'Log', 'warifu' ), function( $post ) {
			warifu_template( 'admin-customer-log', [
				'post' => $post,
			] );
		}, $post_type, 'advanced' );
	}
} );

add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
	if ( false === array_search( $post->post_type, [ 'customer', 'registerd-site' ] ) ) {
		return;
	}
	if ( 'publish' == $old_status && 'private' == $new_status ) {
		// Published post is now deprecated.
		warifu_add_log( __( 'This license is deactivated.', 'warifu' ), true, $post );
	} elseif ( 'publish' == $new_status && 'publish' != $old_status ) {
		// Newly published.
		warifu_add_log( __( 'This license is newly activated!', 'warifu' ), false, $post );
	}
}, 10, 3 );

/**
 * Change comment number
 */
add_filter( 'get_comments_number', function( $count, $post_id ) {
	if ( false !== array_search( get_post_type( $post_id ), [ 'customer', 'registered-site' ] ) ) {
		global $wpdb;
		$query = <<<SQL
			SELECT COUNT( comment_ID ) FROM {$wpdb->comments}
			WHERE comment_post_ID = %d
SQL;
		$count = (int) $wpdb->get_var( $wpdb->prepare( $query, $post_id ) );
	}
	return $count;
}, 10, 2 );
