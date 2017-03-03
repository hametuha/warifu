<?php

/**
 * Register REST root
 *
 * @param WP_Rest_Server
 */
add_action( 'rest_api_init', function( $wp_rest_server ) {

	$root = 'warifu/' . warifu_api_version();

	$license_args = [
		'guid' => [
			'validate_callback' => function( $guid ) {
				return preg_match( '#[0-9A-Za-z\-]+#', $guid );
			},
			'required' => true,
		],
		'license' => [
			'validate_callback' => function( $license ) {
				return ! empty( $license );
			},
			'required' => true,
		],
		'url' => [
			'validate_callback' => function( $url ) {
				return preg_match( '#^https?://#', $url );
			},
			'require' => true,
		],
	];

	// Register update end point
	register_rest_route( $root, '/site/(?<guid>[^/]+)/?', [
		[
			'methods' => 'POST',
			'callback' => function( $params ) {
				// Check product existence
				$product = warifu_get_product( $params['guid'] );
				if ( ! $product ) {
					return new WP_Error( 404, __( 'Product not found.', 'warifu' ), [
						'status' => 404,
					] );
				}
				// Check if already registered and valid.
				$url = untrailingslashit( $params['url'] );
				if ( warifu_site_is_registered( $url, $params['license'], $product ) ) {
					return new WP_REST_Response( [
						'status'  => 200,
						'message' => __( 'This site is already registered.', 'warifu' ),
					] );
				}
				// This site is not registered.
				$site_id = warifu_register_site( $params['license'], $url, 'publish', $product );
				if ( is_wp_error( $site_id ) ) {
					return $site_id;
				} else {
					warifu_add_log( __( 'Site has been registered via REST.', 'warifu' ), false, $site_id );
					return new WP_REST_Response( [
						'status'  => 200,
						'message' => __( 'Your site has been successfully registered.', 'warifu' ),
					] );
				}
			},
			'permission_callback' => false,
			'args' => $license_args,
		],
		[
			'methods' => 'DELETE',
			'callback' => function( $params ) {
				// Check if license exists.
				$product = warifu_get_product( $params['guid'] );
				if ( ! $product ) {
					return new WP_Error( 404, 'Product not found.', [
						'status' => 404,
					] );
				}
				$url = untrailingslashit( $params['url'] );
				$license = trim( $params['license'] );
				if ( $post_id = warifu_site_is_registered( $url, $license, $product ) ) {
					wp_update_post( [
						'ID' => $post_id,
						'post_status' => 'private',
					] );
					warifu_add_log( __( 'Site has been inactivated.', 'warifu' ), false, $post_id );
					return new WP_REST_Response( [
						'status'  => 200,
						'message' => __( 'Your site has been successfully inactivated.', 'warifu' ),
					] );
				} else {
					return new WP_Error( 404, __( 'Site not found.', 'warifu' ), [
						'status' => 404,
					] );
				}
			},
			'permission_callback' => false,
			'args' => $license_args,
		],
	] );


	// Register update end point
	register_rest_route( $root, '/validation/(?<post_id>\\d+)/?', [
		[
			'methods' => 'POST',
			'callback' => function( $params ) {
				$post = get_post( $params['post_id'] );
				if ( ! $post || ! warifu_supported( $post->post_type ) || ! ( $guid  = warifu_guid( $post ) ) ) {
					return new WP_Error( 'invalid_status', __( 'This post is not registered as Gumroad Product.', 'warifu' ), [ 'status' => 400 ] );
				}
				$key = "_warifu_license_{$post->ID}";
				// Check license validity.
				$info = warifu_get_license_info( $guid, $params['license'] );
				if ( is_wp_error( $info ) ) {
					return $info;
				}
				if ( ! warifu_test_license( $info ) ) {
					return new WP_Error( 'invalid_license', __( 'This license is expired or canceled.' ), [ 'status' => 400 ] );
				}
				return new WP_REST_Response( [
					'status' => 200,
				    'data'   => $info,
				] );
			},
			'permission_callback' => function(){
				return current_user_can( 'read' );
			},
			'args' => [
				'post_id' => [
					'validate_callback' => function( $id ) {
						return is_numeric( $id );
					},
					'required' => true,
				],
				'license' => [
					'validate_callback' => function( $license ) {
						return ! empty( $license );
					},
					'required' => true,
				],
				'nonce' => [
					'default' => '',
				],
			],
		],
	] );

	/**
	 * Validator
	 */
	register_rest_route( $root, '/license/(?P<guid>[^/]+)/?', [
		[
			'methods' => 'POST',
			'callback' => function( $params ) {
				$post = warifu_get_product( $params['guid'] );
				if ( ! $post || 'license' != $post->post_type ) {
					return new WP_Error( 404, __( 'Product not found.', 'warifu' ) );
				}
				if ( warifu_site_is_registered( $params['url'], $params['license'], $post ) ) {
					return new WP_REST_Response( [
						'success' => true,
						'message' => __( 'This site is already registered and valid.', 'warifu' ),
					] );
				}
				$response = warifu_register_site( $params['license'], $params['url'], 'publish', $post );
				if ( is_wp_error( $response ) ) {
					if ( 200 == $response->get_error_code() ) {
						// Already registered.
						return new WP_REST_Response( [
							'success' => true,
							'message' => __( 'Your site is already registered.', 'warifu' ),
						] );
					} else {
						return $response;
					}
				} else {
					return new WP_REST_Response( [
						'success' => true,
						'message' => __( 'Your site is successfully added.', 'warifu' ),
					] );
				}
			},
			'args' => [
				'guid' => [
					'required' => true,
				],
				'license' => [
					'required' => true,
					'validate_callback' => function( $var ) {
						return ! empty( $var );
					},
				],
				'url' => [
					'required' => true,
					'validate_callback' => function( $url ) {
						return preg_match( '#^https?://#', $url );
					},
				],
			],
		],
		[
			'methods' => 'DELETE',
			'callback' => function( $params ) {
				$post = warifu_get_product( $params['guid'] );
				if ( ! $post || 'license' != $post->post_type ) {
					return new WP_Error( 404, __( 'Product not found.', 'warifu' ) );
				}
				if ( ! warifu_site_is_registered( $params['url'], $params['license'], $post ) ) {
					return new WP_REST_Response( [
						'success' => true,
						'message' => __( 'This site is not registered.', 'warifu' ),
					] );
				}
				$response = warifu_deactivate_site( $params['url'], $params['license'], $post );

				return new WP_REST_Response( [
					'success' => true,
					'message' => __( 'Your site is successfully unlinked.', 'warifu' ),
				] );
			},
			'args' => [
				'guid' => [
					'required' => true,
				],
				'license' => [
					'required' => true,
					'validate_callback' => function( $var ) {
						return ! empty( $var );
					},
				],
				'url' => [
					'required' => true,
					'validate_callback' => function( $url ) {
						return preg_match( '#^https?://#', $url );
					},
				],
			],
		],
	] );

	// License manager
	register_rest_route( $root, '/license/?', [
		[
			'methods' => 'GET',
		    'callback' => function( $params ) {
			    nocache_headers();
			    $page = max( 1, (int) $params['page'] );
			    $offset = ( $page - 1 ) *  $params['per_page'];
				return new WP_REST_Response( warifu_license_list( get_current_user_id(), $offset, $params['per_page'] ) );
		    },
		    'permission_callback' => function(){
			    return current_user_can( 'read' );
		    },
		    'args' => [
		    	'page' => [
		    		'validate_callback' => function( $page ) {
					    return is_numeric( $page );
				    },
			        'default' => 1,
			    ],
		    	'per_page' => [
		    		'validate_callback' => function( $per_page ) {
					    return is_numeric( $per_page ) && 1 <= $per_page;
				    },
			        'default' => 10,
			    ],
			    'nonce' => [
				    'default' => '',
			    ],
		    ],
		],
	] );

} );
