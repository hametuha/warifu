<?php

/**
 * Add meta box for allowed post types
 */
add_action( 'add_meta_boxes', function( $post_type ) {
	if ( ( 'license' == $post_type ) && warifu_post_types() ) {
		add_meta_box( 'gumroad_license', __( 'Parent Post', 'warifu' ), function( WP_Post $post ) {
			?>
			<label>
				<select name="parent_id">
					<option value="0" <?php selected( ! $post->post_parent ) ?>><?php esc_html_e( 'Undefined', 'warifu' ) ?></option>
					<?php
					/**
					 * warifu_license_parents_args
					 *
					 * @since 1.0.0
					 * @package warifu
					 *
					 * @param array   $args    Parameters passed to `get_posts`
					 * @param WP_Post $post    License post object.
					 * @param string  $context Context of this filter. `admin` or `public`.
					 *
					 * @return array
					 */
					$parents_args = apply_filters( 'warifu_license_parents_args', [
						'post_type'      => warifu_post_types(),
						'posts_per_page' => - 1,
						'post_status'    => 'any',
					], $post, 'admin' );
					$parents      = get_posts( $parents_args );
					foreach ( $parents as $parent ) : ?>
						<option value="<?= esc_attr( $parent->ID ) ?>" <?php selected( $post->post_parent == $parent->ID ) ?>>
							<?php echo esc_html( get_the_title( $parent ) ) ?> (<?php echo esc_html( get_post_type_object( $parent->post_type )->labels->name ) ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php
		}, 'license', 'side' );
	} elseif ( warifu_supported( $post_type ) ) {
		add_meta_box( 'gumsupport', __( 'Registered License', 'warifu' ), function( WP_Post $post ) {
			?>
			<?php if ( $licenses = warifu_license_posts( $post, [ 'post_status' => 'any' ] ) ) : ?>
				<ol class="warifu-license-list">
					<?php foreach ( $licenses as $license ) : ?>
						<li class="warifu-license-item">
							<a href="<?php echo get_edit_post_link( $license->ID ) ?>">
								<?php echo esc_html( get_the_title( $license ) ) ?>
								<small>(
									<?php echo esc_html( sprintf(
										_n( 'For %s site', 'For %s Sites', warifu_limit( $license ), 'warifu' ),
										warifu_limit( $license )
									) ) ?>
								)</small>
							</a>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p class="description">
					<?php echo esc_html( sprintf(
						__( 'This %s has no license.', 'warifu' ),
						get_post_type_object( $post->post_type )->labels->name
					) ) ?>
				</p>
			<?php endif;
		}, $post_type, 'side', 'low' );
	}
} );

