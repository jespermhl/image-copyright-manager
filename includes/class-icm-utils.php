<?php
/**
 * Utility functions
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Utility functions for the plugin
 */
class ICM_Utils {
    
    /**
     * Get attachment ID from img tag
     */
    public static function get_attachment_id_from_img_tag( $img_tag ) {
        // Check for wp-image-{id} class
        if ( preg_match( '/wp-image-(\d+)/', $img_tag, $matches ) ) {
            return intval( $matches[1] );
        }
        
        // Check for data-attachment-id attribute
        if ( preg_match( '/data-attachment-id="(\d+)"/', $img_tag, $matches ) ) {
            return intval( $matches[1] );
        }
        
        // Check for src attribute and convert URL to attachment ID
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
     * Get copyright information for an attachment
     */
    public static function get_copyright_info( $attachment_id ) {
        $copyright = get_post_meta( $attachment_id, '_icm_copyright', true );
        $display_copyright = get_post_meta( $attachment_id, '_icm_display_copyright', true );
        
        return array(
            'copyright' => $copyright,
            'display_copyright' => $display_copyright === '1'
        );
    }
    
    /**
     * Format copyright text with settings
     */
    public static function format_copyright_text( $copyright, $settings = null ) {
        if ( ! $settings ) {
            $settings = ICM_Core::get_settings();
        }
        
        return str_replace( '{copyright}', $copyright, $settings['display_text'] );
    }
    
    /**
     * Sanitize copyright HTML
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
     * Check if current user can manage copyright
     */
    public static function can_manage_copyright() {
        return current_user_can( 'upload_files' );
    }
    
    /**
     * Get plugin version
     */
    public static function get_version() {
        return ICM_Core::VERSION;
    }
    
    /**
     * Get plugin text domain
     */
    public static function get_text_domain() {
        return ICM_Core::TEXT_DOMAIN;
    }
} 