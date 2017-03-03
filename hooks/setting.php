<?php

/**
 * Add setting field to admin screen
 */
add_action( 'admin_init', function(){

	// Add setting section on general.
	add_settings_section(
		'warifu_support_section',
		__( 'Setting for Warifu', 'warifu' ),
		function(){
			printf(
				'<p class="description">%s</p>',
				sprintf(
					__( 'Setting value for Warifu. To use Warifu, you have to know about <a href="%s">Gumroad\'s License Key system</a>.', 'warifu' ),
					'https://help.gumroad.com/11165-Digging-Deeper/license-keys'
				)
			);
		},
		'general'
	);

	// Supported post type
	add_settings_field(
		'warifu_support_post_type',
		__( 'Post types which has license', 'warifu' ),
		function() {
			$post_types = get_post_types( [
				'public' => true,
			] );
			/**
			 * Filter post type to select
			 *
			 * @filter gumsupport_post_type_available
			 * @param array $post_types
			 * @return array
			 */
			$post_types = apply_filters( 'gumsupport_post_type_available', $post_types );
			if ( ! $post_types ) {
				printf( '<p style="color: red;">%s</p>', __( 'No post type available!' ) );
				return;
			}
			$option = (array) get_option( 'warifu_support_post_type', [] );
			foreach ( $post_types as $post_type ) {
				?>
				<label style="margin-right: 1em; display: inline-block">
					<input type="checkbox" name="warifu_support_post_type[]"
					       value="<?php echo esc_attr( $post_type ) ?>" <?php checked( false !== array_search( $post_type, $option ) ) ?> />
					<?php echo esc_html( get_post_type_object( $post_type )->label ) ?>
				</label>
				<?php
			}
			printf(
				'<p class="description">%s</p>',
				__( 'Each post of checked post type will have Gumroad permalink and connected with a product on Gumroad.' )
			);
		},
		'general',
		'warifu_support_section'
	);

	// Register setting field
	register_setting( 'general', 'warifu_support_post_type' );
} );

