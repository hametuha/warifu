<?php
/**
 * Template files
 */

/**
 * Get warifu's REST API Version
 *
 * @return string
 */
function warifu_api_version() {
	return 'v1';
}

/**
 * Load template if possible
 *
 * @param string           $title
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function warifu_form( $title = '', $post = null ) {
	$post = get_post( $post );
	if ( ! warifu_supported( $post->post_type ) ) {
		return false;
	}

	$guid = warifu_guid( $post );
	if ( ! $guid ) {
		return false;
	}
	wp_enqueue_script( 'warifu-helper' );
	wp_localize_script( 'warifu-helper', 'WarifuHelper', [
		'noLicense'  => __( 'License is empty.', 'warifu' ),
	    'msgTimeout' => 5000,
	    'nonce'      => wp_create_nonce( 'wp_rest' ),
	] );
	if ( ! is_user_logged_in() ) {
		warifu_template( 'login', [
			'title' => $title,
		] );
	} else {
		warifu_template( 'form' , [
			'title' => $title,
		] );
	}

	return true;
}

/**
 * Load template
 *
 * @param string $name
 * @param array  $args Will be extracted
 */
function warifu_template( $name, $args = [] ) {
	$path = dirname( __DIR__ ) . '/templates/' . $name . '.php';
	/**
	 * Filter for template path warifu
	 *
	 * @filter warifu_template_path
	 * @param string $path Path to template file
	 * @param string $name Name of template.
	 */
	$path = apply_filters( 'warifu_template_path', $path, $name );
	if ( file_exists( $path ) ) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
		if ( $args ) {
			extract( $args );
		}
		include $path;
	}
}

/**
 * Get asset path
 *
 * @param string $path
 *
 * @return string
 */
function warifu_asset_path( $path ) {
	$url = plugin_dir_url( __DIR__ ).ltrim( $path, '/' );
	/**
	 * Filter for asset URL.
	 *
	 * @filter warifu_asset_path
	 * @param string $url
	 * @param string $path
	 */
	$path = apply_filters( 'warifu_asset_path', $url, $path );
	return $path;
}

/**
 * Return URL on license page
 *
 * @return string
 */
function warifu_return_url() {
	/**
	 * Return URL on license page
	 *
	 * @filter warifu_return_url
	 * @param string $url
	 * @return string
	 */
	return apply_filters( 'warifu_return_url', home_url( '/' ) );
}

/**
 * Get warifu api endpoint
 *
 * @param string $path
 *
 * @return string
 */
function warifu_api_url( $path = '' ) {
	$path = untrailingslashit( ltrim( $path, '/' ) );
	$version = warifu_api_version();
	return trailingslashit( rest_url( "/warifu/{$version}/{$path}" ) );
}

/**
 * Get Endpoint
 *
 * @return string
 */
function warifu_license_center_url() {
	if ( get_option( 'rewrite_rules' ) ) {
		return home_url( '/gumroad/licenses/' );
	} else {
		return add_query_arg( [
			'warifu' => 'license',
		], home_url( '/' ) );
	}
}

/**
 * Gumroad embed tag
 *
 * @param null|int|WP_Post $post
 * @return string
 */
function warifu_gumroad_embed( $post = null ) {
	if ( ! ( $guid = warifu_guid( $post ) ) ) {
		return '';
	}
	wp_enqueue_script( 'gumroad-embed' );
	/**
	 * Filter display of gumroad embed
	 *
	 * @filter warifu_gumroad_embed
	 * @param  array  $vars
	 * @param  string $context
	 * @return array
	 */
	$vars = apply_filters( 'warifu_gumroad_embed', [
		'guid'       => esc_attr( $guid ),
		'class_name' => 'gumroad-product-embed',
	    'url'        => esc_url( "https://gumroad.com/l/{$guid}" ),
	    'label'      => esc_html( __( 'Loading...', 'warifu' ) ),
	], 'embed' );
	return <<<HTML
<div class="{$vars['class_name']}" data-gumroad-product-id="{$vars['guid']}"><a href="{$vars['url']}">{$vars['label']}</a></div>
HTML;
}

/**
 * Show gumroad button
 *
 * @param null|int|WP_Post $post
 * @param array $args
 * @return string
 */
function warifu_gumroad_button( $post = null, $args = [] ) {
	if ( ! ( $guid = warifu_guid( $post ) ) ) {
		return '';
	}
	wp_enqueue_script( 'gumroad-button' );
	$args = wp_parse_args( $args, [
		'class_name' => 'gumroad-button',
		'url'        => esc_url( "https://gum.co/{$guid}" ),
		'label'      => esc_html( __( 'Buy at gumroad', 'warifu' ) ),
	] );
	/**
	 * Filter display of gumroad button
	 *
	 * @filter warifu_gumroad_embed
	 * @param  array  $vars
	 * @param  string $context
	 * @return array
	 */
	$vars = apply_filters( 'warifu_gumroad_embed', $args, 'button' );
	return <<<HTML
<a class="{$vars['class_name']}" href="{$vars['url']}" target="_blank">{$vars['label']}</a>
HTML;
}
