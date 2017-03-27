<?php
/**
 * Cron job
 *
 * @package warifu
 * @since 1.0.0
 */

/**
 * Register cron job
 */
add_action( 'init', function() {
	/**
	 * warifu_should_do_cron
	 *
	 * @since 1.0.0
	 * @package warifu
	 * @param bool $do_cron
	 * @return bool
	 */
	$do_cron = apply_filters( 'warifu_should_do_cron', true );
	if ( ! $do_cron ) {
		return;
	}
	if ( ! wp_next_scheduled( 'warifu_do_cron' ) ) {
		wp_schedule_event( current_time( 'timestamp', true ), 'daily', 'warifu_do_cron' );
	}
} );

/**
 * Do cron
 */
add_action( 'warifu_do_cron', function() {
	// Get all license
	$query = new WP_Query( [
		'post_type' => 'license',
		'posts_per_page' => -1,
		'post_status' => 'any',
	] );
	while ( $query->have_posts() ) {
		$query->the_post();
		$product_id = get_the_ID();
		// Making license list
		$licenses = [];
		foreach ( get_posts( [
			'post_type'   => [ 'customer', 'registered-site' ],
			'post_status' => 'publish',
			'post_parent' => $product_id,
			'posts_per_page' => -1,
		] ) as $owner ) {
			$license = warifu_get_license( $owner );
			if ( ! isset( $licenses[ $license ] ) ) {
				$licenses[ $license ] = [];
			}
			$licenses[ $license ][] = $owner->ID;
		}
		// Process them all
		foreach ( $licenses as $license => $post_ids ) {
			$guid = warifu_guid( $product_id );
			$result = warifu_get_license_info( $guid, $license );
			if ( is_wp_error( $result ) && 'invalid_license' == $result->get_error_code() ) {
				// Oops, this license is invalid! We have to deactivate this license...
				foreach ( $post_ids as $post_id ) {
					warifu_add_log( __( 'Periodical license validation has been failed.', 'warifu' ), true, $post_id );
					wp_update_post( [
						'ID' => $post_id,
						'post_status' => 'private',
					] );
				}
			}
		}
	}
} );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'warifu', 'WarifuCommand' );
}
