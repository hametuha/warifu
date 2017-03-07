<?php
/**
 * License related functions.
 *
 * @param warifu
 * @since 1.0.0
 */


/**
 * Get license list
 *
 * @param int    $user_id
 * @param string $offset
 * @param int    $per_page
 *
 * @return array
 */
function warifu_license_list( $user_id, $offset = 0, $per_page = 10 ) {
	global $wpdb;
	$query = <<<SQL
		SELECT SQL_CALC_FOUND_ROWS
			meta_key, meta_value
		FROM {$wpdb->usermeta}
		WHERE user_id = %d
		  AND meta_key LIKE %s
		ORDER BY umeta_id DESC
		LIMIT %d, %d
SQL;
	$result = [
		'found'    => 0,
		'total'    => 0,
		'offset'   => $offset,
		'per_page' => $per_page,
		'prev'     => $offset > 0,
		'next'     => false,
		'data'     => [],
	];
	$rows = $wpdb->get_results( $wpdb->prepare( $query, $user_id, '_warifu_license_%', $offset, $per_page ) );
	$result['total'] = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );
	$result['found'] = count( $rows );
	if ( $result['total'] > $result['found'] + $offset ) {
		$result['next'] = true;
	}
	foreach ( $rows as $row ) {
		$post_id = (int) str_replace( '_warifu_license_', '', $row->meta_key );
		/**
		 * Filter list item to retrieve
		 *
		 * @filter warifu_license_list_item
		 * @param array  $item
		 * @param int    $user_id
		 * @param string $license
		 * @param int    $post_id
		 */
		$result['data'][] = apply_filters( 'warifu_license_list_item', [
			'key'     => $row->meta_key,
		    'license' => $row->meta_value,
		    'id'      => $post_id,
		    'title'   => get_the_title( $post_id ),
		    'url'     => get_permalink( $post_id ),
		], $user_id, $row->meta_value, $post_id );
	}
	return $result;
}

/**
 * Get GUID for Product
 *
 * @package warifu
 * @since 1.0.0
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function warifu_guid( $post = null ) {
	$post = get_post( $post );
	return get_post_meta( $post->ID, '_warifu_guid', true );
}


/**
 * Get GUID for Product
 *
 * @package warifu
 * @since 1.0.0
 * @param null|int|WP_Post $post
 *
 * @return int
 */
function warifu_limit( $post = null ) {
	$post = get_post( $post );
	return (int) get_post_meta( $post->ID, '_warifu_limit', true );
}

/**
 * Registered customer count.
 *
 * @package warifu
 * @since 1.0.0
 * @param null|int|WP_Post $post
 *
 * @return int
 */
function warifu_customer_count( $post = null ) {
	$post = get_post( $post );
	$query = new WP_Query( [
		'post_type'   => 'customer',
		'post_parent' => $post->ID,
		'post_status' => 'publish',
		'posts_per_page' => -1,
	] );
	return (int) $query->found_posts;
}

/**
 * Get product
 *
 * @param bool|int $user_id
 *
 * @package warifu
 * @since 1.0.0
 * @return array
 */
function warifu_products( $user_id = false ) {
	$args = [
		'post_type' => 'license',
		'posts_per_page' => -1,
	];
	if ( $user_id ) {
		$args['author'] = $user_id;
	}
	return get_posts( $args );
}

/**
 * Get product with GUID.
 *
 * @param string $guid
 *
 * @return null|WP_Post
 */
function warifu_get_product( $guid ) {
	$posts = get_posts( [
		'post_type' => 'license',
		'posts_per_page' => 1,
		'meta_query' => [
			[
				'key' => '_warifu_guid',
				'value' => $guid
			]
		]
	] );
	return $posts ? $posts[0] : null;
}

/**
 * Get license owner
 *
 * @package warifu
 * @since 1.0.0
 * @param string $guid
 * @param string $license
 *
 * @return null|WP_User
 */
function warifu_license_owner( $guid, $license ) {
	$product = warifu_get_product( $guid );
	if ( ! $product ) {
		return null;
	}
	$posts = get_posts( [
		'post_type' => 'customer',
		'posts_per_page' => 1,
		'post_parent' => $product->ID,
		'post_status' => 'any',
		'meta_query' => [
			[
				'key' => '_warifu_license',
				'value' => $license,
			],
		],
	] );
	return $posts ? new WP_User( $posts[0]->post_author ) : null;
}

/**
 * Save site
 *
 * @package warifu
 * @since 1.0.0
 * @param string           $license
 * @param string           $url
 * @param string           $status
 * @param null|int|WP_Post $post
 *
 * @return int|WP_Error
 */
function warifu_register_site( $license, $url, $status = 'publish', $post = null ) {
	$post = get_post( $post );
	if ( ! $post || ( 'license' !== $post->post_type ) ) {
		return new WP_Error( 404, __( 'License not found.', 'warifu' ) );
	}
	$limit = warifu_limit( $post );
	$license = trim( $license );
	$url = untrailingslashit( $url );
	$registered = get_posts( [
		'post_type' => 'registered-site',
		'posts_per_page' => 1,
		'post_parent' => $post->ID,
		'post_status' => 'any',
		'meta_query' => [
			[
				'key' => '_warifu_license',
				'value' => $license,
			],
			[
				'key' => '_warifu_url',
				'value' => $url,
			],
		],
	] );
	// Check if this is duplicated.
	if ( $registered && ( 'publish' == $registered[0]->post_status ) ) {
		return new WP_Error( 200, __( 'This site is already registered.', 'warifu' ), [
			'response' => 200,
		] );
	}
	// Should check limit
	$posts = get_posts( [
		'post_type' => 'registered-site',
		'posts_per_page' => -1,
		'post_parent' => $post->ID,
		'posts_status' => 'publish',
		'meta_query' => [
			[
				'key' => '_warifu_license',
				'value' => $license,
			],
		],
	] );
	if ( 0 < $limit && ( count( $posts ) >= $limit ) ) {
		return new WP_Error( 400, __( 'Site limit exceeded.', 'warifu' ), [
			'response' => 400,
		] );
	}
	// Check license.
	$info = warifu_get_license_info( warifu_guid( $post ), $license );
	if ( is_wp_error( $info ) ) {
		return $info;
	}
	if ( $registered ) {
		// Already exists.
		$update = wp_update_post( [
			'ID' => $registered[0]->ID,
			'post_status' => 'publish',
		], true );
		if ( is_wp_error( $update ) ) {
			return $update;
		} else {
			return $registered[0]->ID;
		}
	} else {
		// Create new
		// O.K. Save information.
		$post_id = wp_insert_post( [
			'post_type'   => 'registered-site',
			'post_title'  => sprintf( '# %d', warifu_biggest_id() ),
			'post_parent' => $post->ID,
			'post_status' => $status,
		], true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		update_post_meta( $post_id, '_warifu_license', $license );
		update_post_meta( $post_id, '_warifu_url', $url );

		return $post_id;
	}
}

/**
 * Check if site is registered.
 *
 * @param warifu
 * @since 1.0.0
 * @param string           $url
 * @param string           $license
 * @param null|int|WP_Post $post
 *
 * @return false|int
 */
function warifu_site_is_registered( $url, $license, $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'license' != $post->post_type ) {
		return false;
	}
	$query = new WP_Query( [
		'post_type'   => 'registered-site',
		'post_status' => 'publish',
		'post_parent' => $post->ID,
		'posts_per_page' => 1,
		'meta_query' => [
			[
				'key'   => '_warifu_license',
				'value' => $license,
			],
			[
				'key'   => '_warifu_url',
				'value' => $url,
			],
		],
	] );
	return $query->have_posts() ? $query->posts[0]->ID : false;
}

/**
 * Deactivate site
 *
 * @param string           $url
 * @param string           $license
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function warifu_deactivate_site( $url, $license, $post = null ) {
	$site_id = warifu_site_is_registered( $url, $license, $post );
	if ( $site_id ) {
		$error = wp_update_post( [
			'ID' => $site_id,
			'post_status' => 'private',
		], true );
		if ( is_wp_error( $error ) ) {
			return $error;
		}
	}
	return true;
}

/**
 * Get license
 *
 * @package warifu
 * @since 1.0.0
 * @param null|int|WP_Post $post customer or registered-site
 *
 * @return string
 */
function warifu_get_license( $post = null ) {
	$post = get_post( $post );
	return (string) get_post_meta( $post->ID, '_warifu_license', true );
}

/**
 * Get registered sites count
 *
 * @package warifu
 * @since 1.0.0
 * @param array $args
 *
 * @return int
 */
function warifu_site_count( $args ) {
	$args = array_merge( $args, [
		'post_type'      => 'registered-site',
		'posts_per_page' => 1,
	] );
	$query = new WP_Query( $args );
	return $query->found_posts;
}

/**
 * Get license object assigned to post.
 *
 * @param null|int|WP_Post $post If not set, current post.
 * @param array            $args Override array to get_posts
 *
 * @return array
 */
function warifu_license_posts( $post = null, $args = [] ) {
	$post = get_post( $post );
	$args = wp_parse_args( $args, [
		'post_type'  => 'license',
		'post_status' => 'publish',
		'post_parent' => $post->ID,
		'posts_per_page' => -1,
		'meta_key' => '_warifu_limit',
		'orderby' => [ 'meta_value_num' => 'ASC' ],
	] );
	return get_posts( $args );
}

/**
 * Get status label
 *
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function warifu_status_label( $post = null ) {
	$post = get_post( $post );
	switch ( $post->post_status ) {
		case 'publish':
			$color = 'green';
			$label = __( 'Active', 'warifu' );
			$icon  = 'yes';
			break;
		case 'private':
			$color = 'grey';
			$label = __( 'Inactive', 'warifu' );
			$icon  = 'minus';
			break;
		default:
			$color = 'red';
			$label = __( 'Undefined', 'warifu' );
			$icon  = 'no';
			break;
	}
	return sprintf(
		'<span style="color: %1$s"><span class="dashicons dashicons-%3$s"></span> %2$s</span>',
		esc_attr( $color ),
		esc_html( $label ),
		esc_attr( $icon )
	);
}

function warifu_last_checked( $post = null ) {
	$post = get_post( $post );
	$date = get_post_meta( $post->ID, '_warifu', true );
}
