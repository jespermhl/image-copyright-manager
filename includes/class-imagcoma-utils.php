<?php
/**
 * Utility functions
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IMAGCOMA_Utils {
    
    /**
     * Extracts attachment ID from an <img> tag.
     *
     * @param string $img_tag The full <img> HTML tag.
     * @return int|bool Attachment ID or false if not found.
     */
    public static function get_attachment_id_from_img_tag( $img_tag ) {
        if ( preg_match( '/wp-image-(\d+)/', $img_tag, $matches ) ) {
            return intval( $matches[1] );
        }
        
        if ( preg_match( '/data-attachment-id="(\d+)"/', $img_tag, $matches ) ) {
            return intval( $matches[1] );
        }
        
        if ( preg_match( '/src=["\']([^"\']+)["\']/', $img_tag, $matches ) ) {
            $url = $matches[1];
            $attachment_id = attachment_url_to_postid( $url );
            if ( $attachment_id ) {
                return $attachment_id;
            }
        }
        
        return false;
    }
    
    /**
     * Retrieves copyright information for a specific attachment.
     *
     * @param int $attachment_id The attachment ID.
     * @return array Copyright data including text, creator, and license info.
     */
    public static function get_copyright_info( $attachment_id ) {
        $cache_key = 'imagcoma_copyright_' . $attachment_id;
        $cached_data = wp_cache_get( $cache_key, 'imagcoma' );

        if ( false !== $cached_data ) {
            return $cached_data;
        }

        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT copyright_text, creator, copyright_notice, credit_text, license_url, acquire_license_url FROM {$wpdb->prefix}imagcoma_copyright WHERE attachment_id = %d", $attachment_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table with object cache
        $display_copyright = get_post_meta( $attachment_id, '_imagcoma_display_copyright', true );
        
        $data = array(
            'copyright'           => $row ? ($row->copyright_text ?? '') : '',
            'creator'             => $row ? ($row->creator ?? '') : '',
            'copyright_notice'    => $row ? ($row->copyright_notice ?? '') : '',
            'credit_text'         => $row ? ($row->credit_text ?? '') : '',
            'license_url'         => $row ? ($row->license_url ?? '') : '',
            'acquire_license_url' => $row ? ($row->acquire_license_url ?? '') : '',
            'display_copyright'   => $display_copyright === '1'
        );

        wp_cache_set( $cache_key, $data, 'imagcoma', HOUR_IN_SECONDS );

        return $data;
    }
    
    /**
     * Formats copyright text based on plugin settings.
     *
     * @param string $copyright The raw copyright text.
     * @param array|null $settings Plugin settings (optional).
     * @return string Formatted copyright text.
     */
    public static function format_copyright_text( $copyright, $settings = null ) {
        if ( ! $settings ) {
            $settings = IMAGCOMA_Core::get_settings();
        }
        
        return str_replace( '{copyright}', $copyright, $settings['display_text'] );
    }
    
    /**
     * Sanitizes HTML content for copyright information.
     *
     * @param string $content The raw HTML content.
     * @return string Sanitized HTML content.
     */
    public static function sanitize_copyright_html( $content ) {
        $allowed_html = array(
            'a' => array(
                'href' => array(),
                'title' => array(),
                'target' => array(),
                'rel' => array()
            ),
            'br' => array(),
            'em' => array(),
            'strong' => array(),
            'span' => array(
                'class' => array()
            )
        );
        
        return wp_kses( $content, $allowed_html );
    }
    
    /**
     * Checks if the current user can manage copyright information.
     *
     * @return bool True if the user can manage copyright.
     */
    public static function can_manage_copyright() {
        return current_user_can( 'upload_files' );
    }
    
    /**
     * Gets the current plugin version.
     *
     * @return string Plugin version.
     */
    public static function get_version() {
        return IMAGCOMA_Core::VERSION;
    }
    
    /**
     * Gets the plugin text domain.
     *
     * @return string Text domain.
     */
    public static function get_text_domain() {
        return IMAGCOMA_Core::TEXT_DOMAIN;
    }

    /**
     * Saves copyright information to the database.
     *
     * @param int $attachment_id The attachment ID.
     * @param string $copyright_text The copyright text.
     * @param string $creator The creator name.
     * @param string $copyright_notice The copyright notice.
     * @param string $credit_text The credit text.
     * @param string $license_url The license URL.
     * @param string $acquire_license_url The acquire license URL.
     */
    public static function save_copyright_info( $attachment_id, $copyright_text, $creator = '', $copyright_notice = '', $credit_text = '', $license_url = '', $acquire_license_url = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'imagcoma_copyright';

        $wpdb->replace( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write with cache invalidation
            $table_name,
            array(
                'attachment_id'       => $attachment_id,
                'copyright_text'      => $copyright_text,
                'creator'             => $creator,
                'copyright_notice'    => $copyright_notice,
                'credit_text'         => $credit_text,
                'license_url'         => $license_url,
                'acquire_license_url' => $acquire_license_url,
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );

        // Sync to post meta for REST API access (required by WP 7.0+ DataViews)
        $meta_map = array(
            'imagcoma_copyright'           => $copyright_text,
            'imagcoma_creator'             => $creator,
            'imagcoma_copyright_notice'    => $copyright_notice,
            'imagcoma_credit_text'         => $credit_text,
            'imagcoma_license_url'         => $license_url,
            'imagcoma_acquire_license_url' => $acquire_license_url,
        );
        foreach ( $meta_map as $key => $value ) {
            update_post_meta( $attachment_id, $key, $value );
        }

        wp_cache_delete( 'imagcoma_copyright_' . $attachment_id, 'imagcoma' );
        wp_cache_delete( 'imagcoma_attachments_with_copyright', 'imagcoma' );
    }

    /**
     * Gets all attachment IDs that have copyright information.
     *
     * @param string $orderby Sort field: 'date', 'title', 'attachment_id', or 'copyright_text'.
     * @param string $order   Sort direction: 'ASC' or 'DESC'.
     * @return array List of objects containing attachment_id and copyright_text.
     */
    public static function get_attachments_with_copyright( $orderby = 'date', $order = 'DESC' ) {
        global $wpdb;

        $allowed_orderby = array( 'attachment_id', 'date', 'title', 'copyright_text' );
        $allowed_order   = array( 'ASC', 'DESC' );

        $orderby = in_array( strtolower( $orderby ), $allowed_orderby, true ) ? strtolower( $orderby ) : 'date';
        $order   = in_array( strtoupper( $order ), $allowed_order, true ) ? strtoupper( $order ) : 'DESC';

        $cache_key = 'imagcoma_attachments_with_copyright';
        $results   = wp_cache_get( $cache_key, 'imagcoma' );
        if ( false === $results ) {
            $results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table with object cache
                $wpdb->prepare(
                    "SELECT attachment_id, copyright_text FROM {$wpdb->prefix}imagcoma_copyright WHERE copyright_text != %s",
                    ''
                )
            );
            if ( is_array( $results ) ) {
                wp_cache_set( $cache_key, $results, 'imagcoma', HOUR_IN_SECONDS );
            }
        }

        if ( empty( $results ) ) {
            return array();
        }

        // Fields available in the cached results
        if ( in_array( $orderby, array( 'attachment_id', 'copyright_text' ), true ) ) {
            usort( $results, function ( $a, $b ) use ( $orderby, $order ) {
                if ( 'attachment_id' === $orderby ) {
                    $cmp = $a->attachment_id - $b->attachment_id;
                } else {
                    $cmp = strcmp( $a->copyright_text, $b->copyright_text );
                }
                return 'ASC' === $order ? $cmp : -$cmp;
            } );
            return $results;
        }

        // For date or title ordering, fetch post data
        $ids = wp_list_pluck( $results, 'attachment_id' );
        if ( empty( $ids ) ) {
            return array();
        }

        $posts = get_posts( array(
            'post_type'      => 'attachment',
            'post__in'       => $ids,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'orderby'        => 'date' === $orderby ? 'date' : 'title',
            'order'          => $order,
        ) );

        // Build an ID → row map for O(1) lookups
        $row_map = array();
        foreach ( $results as $row ) {
            $row_map[ (int) $row->attachment_id ] = $row;
        }

        // Collect rows in the order returned by get_posts
        $sorted = array();
        foreach ( $posts as $post ) {
            $id = $post->ID;
            if ( isset( $row_map[ $id ] ) ) {
                $sorted[] = $row_map[ $id ];
                unset( $row_map[ $id ] );
            }
        }
        // Append any remaining IDs not in get_posts result
        foreach ( $row_map as $row ) {
            $sorted[] = $row;
        }

        return $sorted;
    }
} 