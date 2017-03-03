<?php

/**
 * Add query vars
 *
 * @param array $vars
 * @return array
 */
add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'warifu';
	return $vars;
} );

/**
 * Register rewrite rules.
 *
 * @param array $rules
 * @return array
 */
add_filter( 'rewrite_rules_array', function( $rules ) {
	return array_merge( [
		'^gumroad/([^/]+)/?$' => 'index.php?warifu=$matches[1]',
	], $rules );
} );

/**
 * Hijack query
 */
add_action( 'pre_get_posts', function( &$wp_query ) {
	if ( ! is_admin() &&  $wp_query->is_main_query() && ( $action = $wp_query->get( 'warifu' ) ) ) {
		switch ( $action ) {
			case 'licenses':
				nocache_headers();
				if ( ! is_user_logged_in() ) {
					auth_redirect();
					exit;
				}
				warifu_template( 'licenses' );
				exit;
				break;
			default:
				$wp_query->set( 'warifu', null );
				$wp_query->set_404();
				break;
		}
	}
} );
