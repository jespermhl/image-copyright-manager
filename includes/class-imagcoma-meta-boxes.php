<?php
/**
 * Meta boxes functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IMAGCOMA_Meta_Boxes {
    
    public function __construct() {
        add_filter( 'attachment_fields_to_edit', array( $this, 'add_copyright_field_to_media_modal' ), 10, 2 );
        add_filter( 'attachment_fields_to_save', array( $this, 'save_copyright_data' ), 10, 2 );
    }
    
    public function add_copyright_field_to_media_modal( $form_fields, $post ) {
        $copyright_data = IMAGCOMA_Utils::get_copyright_info( $post->ID );
        $copyright = $copyright_data['copyright'] ?? '';
        $display_copyright = $copyright_data['display_copyright'] ?? false;
        
        $form_fields['imagcoma_copyright'] = array(
            'label' => __( 'Copyright Info', 'image-copyright-manager' ),
            'input' => 'textarea',
            'value' => $copyright,
            'helps' => __( 'Enter copyright information. HTML links are allowed.', 'image-copyright-manager' ),
            'show_in_edit' => true,
            'show_in_modal' => true,
        );

        $form_fields['imagcoma_display_copyright'] = array(
            'label' => __( 'Display Copyright', 'image-copyright-manager' ),
            'input' => 'html',
            'html'  => '<label><input type="checkbox" name="attachments[' . $post->ID . '][imagcoma_display_copyright]" value="1" ' . checked( $display_copyright, true, false ) . ' /> ' . __( 'Display copyright text under this image', 'image-copyright-manager' ) . '</label>',
            'show_in_edit' => true,
            'show_in_modal' => true,
        );
        
        return $form_fields;
    }
    
    public function save_copyright_data( $post, $attachment ) {
        if ( isset( $attachment['imagcoma_copyright'] ) ) {
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
            
            $copyright_data = wp_kses( wp_unslash( $attachment['imagcoma_copyright'] ), $allowed_html );
            IMAGCOMA_Utils::save_copyright_info( $post['ID'], $copyright_data );
        }
        
        $display_copyright = isset( $attachment['imagcoma_display_copyright'] ) ? '1' : '0';
        update_post_meta( $post['ID'], '_imagcoma_display_copyright', $display_copyright );

        return $post;
    }
} 