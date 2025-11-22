<?php
/**
 * Admin Columns functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IMAGCOMA_Admin_Columns {

	public function __construct() {
		add_filter( 'manage_media_columns', array( $this, 'add_copyright_column' ) );
		add_action( 'manage_media_custom_column', array( $this, 'display_copyright_column' ), 10, 2 );
		add_filter( 'manage_upload_sortable_columns', array( $this, 'register_sortable_column' ) );
		add_filter( 'posts_join_paged', array( $this, 'copyright_column_join' ), 10, 2 );
		add_filter( 'posts_orderby', array( $this, 'copyright_column_orderby' ), 10, 2 );
	}

	/**
	 * Add the copyright column to the media library
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_copyright_column( $columns ) {
		$columns['imagcoma_copyright'] = __( 'Copyright', 'image-copyright-manager' );
		return $columns;
	}

	/**
	 * Display the content of the copyright column
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Attachment ID.
	 */
	public function display_copyright_column( $column_name, $post_id ) {
		if ( 'imagcoma_copyright' !== $column_name ) {
			return;
		}

		$copyright_data = IMAGCOMA_Utils::get_copyright_info( $post_id );
		$copyright      = $copyright_data['copyright'] ?? '';

		if ( ! empty( $copyright ) ) {
			echo wp_kses_post( $copyright );
		} else {
			echo '<span aria-hidden="true">—</span>';
		}
	}

	/**
	 * Register the column as sortable
	 *
	 * @param array $columns Existing sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function register_sortable_column( $columns ) {
		$columns['imagcoma_copyright'] = 'imagcoma_copyright';
		return $columns;
	}

	/**
	 * Join the custom table when sorting by copyright
	 *
	 * @param string   $join  The JOIN clause.
	 * @param WP_Query $query The current WP_Query instance.
	 * @return string Modified JOIN clause.
	 */
	public function copyright_column_join( $join, $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return $join;
		}

		if ( 'imagcoma_copyright' === $query->get( 'orderby' ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'imagcoma_copyright';
			$join      .= " LEFT JOIN $table_name ON $wpdb->posts.ID = $table_name.attachment_id";
		}

		return $join;
	}

	/**
	 * Order by the custom table column
	 *
	 * @param string   $orderby The ORDER BY clause.
	 * @param WP_Query $query   The current WP_Query instance.
	 * @return string Modified ORDER BY clause.
	 */
	public function copyright_column_orderby( $orderby, $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return $orderby;
		}

		if ( 'imagcoma_copyright' === $query->get( 'orderby' ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'imagcoma_copyright';
			$order      = $query->get( 'order' ) ? $query->get( 'order' ) : 'ASC';
			$orderby    = "$table_name.copyright_text $order";
		}

		return $orderby;
	}
}
