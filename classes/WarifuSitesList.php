<?php
if ( ! is_admin() ) {
	return;
}

/**
 * List table for Warifu registered site
 *
 * @package warifu
 * @since 1.0.0
 */
class WarifuSitesList extends WP_List_Table {

	/**
	 * Constructor
	 *
	 * @param array $args
	 */
	public function __construct( $args = [] ) {
		parent::__construct( [
			'singular'  => 'site',
			'plural'    => 'sites',
			'ajax'      => false,
		] );
	}

	/**
	 * Column name.
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'cb'     => '<input type="checkbox" />',
			'url'    => 'URL',
			'parent' => 'GUID',
			'owner'   => __( 'Owner', 'warifu' ),
			'status' => __( 'Status', 'warifu' ),
			'registered' => __( 'Registered', 'warifu' ),
		];
	}

	/**
	 * Register sortable columns
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		return [
			'count'      => [ 'count',false ],
			'registered' => [ 'registered',true ],
		];
	}

	/**
	 * Update status
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return [
			'invalidate' => __( 'Invalidate', 'warifu' ),
			'activate'   => __( 'Activate', 'warifu' ),
			'inactivate' => __( 'Inactivate', 'warifu' ),
			'delete'     => __( 'Delete', 'warifu' ),
		];
	}

	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...
		if ( 'delete'===$this->current_action() ) {
			wp_die('Items deleted (or they would be if we had items to delete)!');
		}

	}


	/**
	 * Get column's checkbox.
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function column_cb( $post ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'registered-site',
			$post->ID
		);
	}

	/**
	 * Render row
	 *
	 * @param WP_Post $post
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $post, $column_name ) {
		switch ( $column_name ) {
			case 'url':
				$url = get_post_meta( $post->ID, '_warifu_url', true );
				$html = $url ? sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $url ) ) : '---';
				$html .= $this->row_actions( [
					'logs' => '<a href=""></a>',
					'edit' => '<a href=""></a>',
				] );
				return $html;
				break;
			case 'parent':
				return sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( "edit.php?post_type=license&page=registered-sites&parent=$post->post_parent" ) ),
					warifu_guid( $post->post_parent )
				);
				break;
			case 'owner':
				if ( $owner = warifu_license_owner( warifu_guid( $post->post_parent ), warifu_get_license( $post ) ) ) {
					return sprintf(
						'<a href="%s">%s</a>',
						esc_url( admin_url( "edit.php?post_type=license&page=registered-sites&owner={$owner->ID}" ) ),
						esc_html( $owner->display_name )
					);
				} else {
					return '---';
				}
				break;
			case 'status':
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
				break;
			case 'registered':
				return get_the_date( '', $post ) . ' ' . get_the_time( '', $post );
				break;
			default:
				// Do nothing.
				break;
		}
	}

	/**
	 * Register items.
	 */
	public function prepare_items() {
		$per_page = 20;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
		$this->process_bulk_action();
		$args = [
			'post_type'      => 'registered-site',
			'posts_per_page' => $per_page,
			'paged'          => max( 1, $this->get_pagenum() ),
			'orderby' => 'date',
		];
		$args['order'] = ( isset( $_GET['registered'] ) && 'asc' == $_GET['registered'] )
			? 'asc'
			: 'desc';
		if ( isset( $_GET['parent'] ) ) {
			$args['post_parent'] = $_GET['parent'];
		}
		$query = new WP_Query( $args );
		$this->items = $query->posts;
		$this->set_pagination_args( [
			'total_items' => $query->found_posts,
			'per_page'    => $per_page,
			'total_pages' => $query->max_num_pages,
		] );
	}


}
