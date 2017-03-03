<?php
/**
 * Utility functions
 *
 * @package warifu
 * @since 1.0.0
 */

/**
 * Add log
 *
 * @package warifu
 * @since 1.0.0
 * @param string           $log Log text.
 * @param bool             $error Default false.
 * @param null|int|WP_Post $post
 * @param array            $args Comment's arg array.
 *
 * @return bool|WP_Error
 */
function warifu_add_log( $log, $error = false, $post = null, $args = [] ) {
	$args = wp_parse_args( $args, [
		'comment_author'     => get_bloginfo( 'name' ),
		'comment_author_IP'  => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
		'comment_author_url' => home_url(),
		'comment_agent'      => 'Wafifu-Admin',
		'user_id'            => 0,
	] );
	// Set values
	$post = get_post( $post );
	$args['comment_post_ID'] = $post->ID;
	$args['comment_content'] = $log;
	$args['comment_approved'] = $error ? '' : '1';
	$args['comment_type'] = 'warifu_log';
	$args['comment_approved'] = $error ? '0' : '1';
	return wp_insert_comment( $args )
		? true
		: new WP_Error( 500, __( 'Failed to add log.', 'warifu' ) );
}

/**
 * Render log line.
 *
 * @param WP_Comment $comment
 */
function warifu_render_log( $comment ) {
	?>
	<li class="warifu-log-item <?php echo esc_attr( $comment->comment_approved ? '' : ' warifu-log-item-error' ) ?>">
		<div class="warifu-log-text">
			<?php echo wpautop( $comment->comment_content ) ?>
		</div>
		<div class="warifu-log-meta">
			<span class="warifu-log-meta-item">
				<span class="dashicons dashicons-calendar"></span>
				<?php echo mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $comment->comment_date ) ?>
			</span>
			<span class="warifu-log-meta-item">
				<span class="dashicons dashicons-warning"></span>
				<?php echo esc_html( $comment->comment_author_IP ) ?>
			</span>
		</div>
	</li>
	<?php
}


/**
 * Get max number for Licensed sites.
 *
 * @package warifu
 * @since 1.0.0
 * @return null|string
 */
function warifu_biggest_id() {
	global $wpdb;
	$query = <<<SQL
		SELECT MAX(ID) FROM {$wpdb->posts}
SQL;
	return $wpdb->get_var( $query ) + 1;
}

/**
 * Get Gumroad license key of user
 *
 * @param int $user_id
 * @param int $post_id
 *
 * @return string
 */
function warifu_user_key ( $user_id, $post_id ) {
	$key = "_warifu_license_{$post_id}";
	return (string) get_user_meta( $user_id, $key, true );
}

/**
 * Show drop down
 *
 * @param string $name
 * @param int    $current
 * @param string $id If not set, $name will be used.
 */
function warifu_pull_down( $name, $current = 0, $id = '' ) {
	if ( ! $id ) {
		$id = $name;
	}
	?>
	<select id="<?php echo esc_attr( $id ) ?>" name="<?php echo esc_attr( $name ) ?>">
		<option value="0" <?php selected( $current == 0 ) ?>>
			<?php esc_html_e( 'Please select', 'warifu' ) ?>
		</option>
		<?php foreach ( warifu_products( current_user_can( 'edit_others_posts' ) ? 0 : get_current_user_id() ) as $post ) : ?>
			<option value="<?php echo esc_attr( $post->ID ) ?>"<?php selected( $post->ID == $current ) ?>>
				<?php echo esc_html( get_the_title( $post ) ) ?>
				( <?php echo esc_html( warifu_guid( $post ) ) ?> )
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
