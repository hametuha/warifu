<?php

/**
 * If post is deleted, remove all license key.
 */
add_action( 'delete_post', function ( $post_id ) {
	global $wpdb;
	$key   = "_warifu_license_{$post_id}";
	$query = <<<SQL
		DELETE FROM {$wpdb->postmeta}
		WHERE meta_key = %s
SQL;
	$wpdb->query( $wpdb->prepare( $query, $key ) );
} );

/**
 * Regsiter post type
 */
add_action( 'init', function () {
	$args = [
		'label'            => __( 'Licenses', 'warifu' ),
		'labels'           => [
			'singular_name' => __( 'License', 'warifu' ),
		],
		'public'           => false,
		'show_ui'          => true,
		'show_in_nav_menu' => false,
		'supports'         => [ 'title', 'editor', 'author' ],
		'menu_icon'        => 'dashicons-tickets-alt',
	];
	/**
	 * warifu_post_type_args
	 *
	 * @since 1.0.0
	 * @package warifu
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	$args = apply_filters( 'warifu_post_type_args', $args );
	register_post_type( 'license', $args );

} );


/**
 * Add meta box for allowed post types
 */
add_action( 'add_meta_boxes', function ( $post_type ) {
	if ( 'license' !== $post_type ) {
		return;
	}
	// License register box
	add_meta_box( 'license_info', __( 'License Setting', 'warifu' ), function ( WP_Post $post ) {
		wp_nonce_field( 'warifu_guid', '_warifunonce', false );
		?>
		<p>
			<?php
			$count = warifu_customer_count( $post );
			if ( $count ) {
				printf(
					'<a href="%s">%s</a>',
					admin_url( "edit.php?post_type=license&page=registered-sites&parent={$post->ID}" ),
					_n( '%d site registered.', '%d sites registered.', $count, 'warifu' )
				);
			} else {
				esc_html_e( 'No site registered.', 'warifu' );
			}
			?>
		</p>
		<table class="form-table form-table-warifu">
			<tr>
				<th valign="top">
					<label for="warifu_guid"><?php _e( 'Product Permalink for Gumroad', 'warifu' ) ?></label>
				</th>
				<td>
					<input class="regular-text" type="text" name="warifu_guid" id="warifu_guid"
						   value="<?php echo esc_attr( warifu_guid( $post ) ) ?>"/>
					<p class="description">
						<?php _e( 'Product permalink is the last segment for URL.', 'warifu' ) ?>
						<?php _e( 'if your product\'s URI is <code>https://gumroad.com/l/QMGY</code>, permalink would be <code>OMGY</code>.', 'warifu' ) ?>
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="warifu_limit">
						<?php esc_html_e( 'Register Limit', 'warifu' ) ?>
					</label>
				</th>
				<td>
					<input class="regular-text" type="text" name="warifu_limit" id="warifu_limit"
						   value="<?php echo esc_attr( warifu_limit( $post ) ) ?>"/>
					<p class="description">
						<?php _e( 'Registration limit for this license. 0 means infinite.', 'warifu' ) ?>
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="warifu_url">
						<?php esc_html_e( 'Validate URL', 'warifu' ) ?>
					</label>
				</th>
				<td>
					<?php if ( $guid = warifu_guid( $post ) ) : ?>
						<input class="regular-text" type="text" name="warifu_url" id="warifu_url"
							   value="<?php echo esc_url( rest_url( "/warifu/v1/license/{$guid}/" ) ) ?>" readonly/>
						<p class="description">
							<?php _e( 'This URL is for validation.', 'warifu' ) ?>
						</p>
					<?php else : ?>
						<p class="description">
							<?php esc_html_e( 'After saving GUID, you can get URL!', 'warifu' ) ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<hr/>
		<?php if ( warifu_guid( $post ) ) : ?>
			<?php echo warifu_gumroad_embed( $post ) ?>
		<?php else : ?>
			<p class="description">
				<?php esc_html_e( 'If you enter Gumroad GUID, widgets will be embed', 'warifu' ) ?>
			</p>
		<?php endif; ?>
		<?php
	}, $post_type, 'normal', 'high' );
} );

/**
 * Save post information.
 *
 * @param int $post_id
 * @param WP_Post $post
 */
add_action( 'save_post', function ( $post_id, $post ) {
	// Check post_type
	if ( 'license' !== $post->post_type ) {
		return;
	}
	// Check nonce.
	if ( ! isset( $_POST['_warifunonce'] ) || ! wp_verify_nonce( $_POST['_warifunonce'], 'warifu_guid' ) ) {
		return;
	}
	// Save information.
	foreach ( [ 'warifu_guid', 'warifu_limit' ] as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, '_' . $key, $_POST[ $key ] );
		}
	}
}, 10, 2 );

/**
 * Add column to list table
 */
add_filter( 'manage_license_posts_columns', function ( $columns ) {
	$new_columns = [];
	foreach ( $columns as $key => $val ) {
		$new_columns[ $key ] = $val;
		if ( 'title' == $key ) {
			$new_columns['guid']  = 'GUID';
			$new_columns['count'] = __( 'Site', 'warifu' );
		}
	}
	$new_columns['parent'] = __( 'Parent', 'warifu' );

	return $new_columns;
} );

/**
 * Add license column
 */
add_action( 'manage_license_posts_custom_column', function ( $column, $post_id ) {
	switch ( $column ) {
		case 'guid':
			if ( $license = warifu_guid( $post_id ) ) {
				printf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( "edit.php?post_type=license&page=registered-sites&parent={$post_id}" ) ),
					esc_html( $license )
				);
			} else {
				echo '---';
			}
			break;
		case 'parent':
			$post = get_post( $post_id );
			if ( $post->post_parent ) {
				printf(
					'<a href="%s">%s</a>',
					get_edit_post_link( $post->post_parent ),
					esc_html( get_the_title( $post->post_parent ) )
				);
			} else {
				echo '---';
			}
			break;
		case 'count':
			$count = warifu_site_count( [
				'post_parent' => $post_id,
			] );
			printf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( "edit.php?post_type=license&page=registered-sites&parent={$post_id}" ) ),
				number_format( $count )
			);
			break;
	}
}, 10, 2 );
