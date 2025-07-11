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
    
    public static function get_copyright_info( $attachment_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'imagcoma_copyright';
        $copyright = $wpdb->get_var( $wpdb->prepare( "SELECT copyright_text FROM $table_name WHERE attachment_id = %d", $attachment_id ) );
        $display_copyright = get_post_meta( $attachment_id, '_imagcoma_display_copyright', true );
        
        return array(
            'copyright' => $copyright,
            'display_copyright' => $display_copyright === '1'
        );
    }
    
    public static function format_copyright_text( $copyright, $settings = null ) {
        if ( ! $settings ) {
            $settings = IMAGCOMA_Core::get_settings();
        }
        
        return str_replace( '{copyright}', $copyright, $settings['display_text'] );
    }
    
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
    
    public static function can_manage_copyright() {
        return current_user_can( 'upload_files' );
    }
    
    public static function get_version() {
        return IMAGCOMA_Core::VERSION;
    }
    
    public static function get_text_domain() {
        return IMAGCOMA_Core::TEXT_DOMAIN;
    }

    public static function save_copyright_info( $attachment_id, $copyright_text ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'imagcoma_copyright';

        $wpdb->replace(
            $table_name,
            array(
                'attachment_id'   => $attachment_id,
                'copyright_text'  => $copyright_text,
            ),
            array(
                '%d',
                '%s',
            )
        );
    }

    public static function get_attachments_with_copyright() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'imagcoma_copyright';
  
        $cache_key = 'imagcoma_attachments_with_copyright';
        $results = wp_cache_get( $cache_key, 'imagcoma' );
        if ( false === $results ) {
            $sql = $wpdb->prepare(
                "SELECT attachment_id, copyright_text FROM $table_name WHERE copyright_text != %s",
                ''
            );
            $results = $wpdb->get_results( $sql );
            wp_cache_set( $cache_key, $results, 'imagcoma', HOUR_IN_SECONDS );
        }
        return $results;
    }
} 