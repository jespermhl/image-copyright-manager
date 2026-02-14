<?php
/**
 * Meta boxes functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles the creation and saving of custom fields for image copyright information in the media modal.
 */
class IMAGCOMA_Meta_Boxes {
    
    /**
     * Constructor.
     * Initializes hooks for adding and saving copyright fields.
     */
    public function __construct() {
        add_filter( 'attachment_fields_to_edit', array( $this, 'add_copyright_field_to_media_modal' ), 10, 2 );
        add_filter( 'attachment_fields_to_save', array( $this, 'save_copyright_data' ), 10, 2 );
    }
    
    /**
     * Adds custom copyright fields to the media edit screen and modal.
     *
     * @param array $form_fields List of form fields.
     * @param WP_Post $post The current attachment post object.
     * @return array Modified form fields.
     */
    public function add_copyright_field_to_media_modal( $form_fields, $post ) {
        $copyright_data = IMAGCOMA_Utils::get_copyright_info( $post->ID );
        $copyright = $copyright_data['copyright'] ?? '';
        $creator = $copyright_data['creator'] ?? '';
        $copyright_notice = $copyright_data['copyright_notice'] ?? '';
        $credit_text = $copyright_data['credit_text'] ?? '';
        $license_url = $copyright_data['license_url'] ?? '';
        $acquire_license_url = $copyright_data['acquire_license_url'] ?? '';
        $display_copyright = $copyright_data['display_copyright'] ?? false;
        
        $form_fields['imagcoma_copyright'] = array(
            'label' => __( 'Copyright Info', 'image-copyright-manager' ),
            'input' => 'textarea',
            'value' => $copyright,
            'helps' => __( 'Enter copyright information for display on the website. HTML links are allowed.', 'image-copyright-manager' ),
            'show_in_edit' => true,
            'show_in_modal' => true,
        );

        $form_fields['imagcoma_creator'] = array(
            'label' => __( 'Creator (SEO)', 'image-copyright-manager' ),
            'input' => 'text',
            'value' => $creator,
            'helps' => __( 'The person or entity that created the image.', 'image-copyright-manager' ),
            'show_in_edit' => true,
            'show_in_modal' => true,
        );

        $form_fields['imagcoma_copyright_notice'] = array(
            'label' => __( 'Copyright Notice (SEO)', 'image-copyright-manager' ),
            'input' => 'text',
            'value' => $copyright_notice,
            'helps' => __( 'The copyright notice for Google SEO.', 'image-copyright-manager' ),
            'show_in_edit' => true,
            'show_in_modal' => true,
        );

        $form_fields['imagcoma_credit_text'] = array(
            'label' => __( 'Credit Text (SEO)', 'image-copyright-manager' ),
            'input' => 'text',
            'value' => $credit_text,
            'helps' => __( 'The credit text for the image (e.g., photographer name).', 'image-copyright-manager' ),
            'show_in_edit' => true,
            'show_in_modal' => true,
        );

        $form_fields['imagcoma_license_url'] = array(
            'label' => __( 'License URL (SEO)', 'image-copyright-manager' ),
            'input' => 'text',
            'value' => $license_url,
            'helps' => __( 'URL to the license of the image.', 'image-copyright-manager' ),
            'show_in_edit' => true,
            'show_in_modal' => true,
        );

        $form_fields['imagcoma_acquire_license_url'] = array(
            'label' => __( 'Acquire License URL (SEO)', 'image-copyright-manager' ),
            'input' => 'text',
            'value' => $acquire_license_url,
            'helps' => __( 'URL where a user can acquire a license for the image.', 'image-copyright-manager' ),
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
    
    /**
     * Saves the custom copyright data when an attachment is updated.
     *
     * @param array $post The post data.
     * @param array $attachment The attachment data.
     * @return array Modified post data.
     */
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
            $creator = isset( $attachment['imagcoma_creator'] ) ? sanitize_text_field( wp_unslash( $attachment['imagcoma_creator'] ) ) : '';
            $copyright_notice = isset( $attachment['imagcoma_copyright_notice'] ) ? sanitize_text_field( wp_unslash( $attachment['imagcoma_copyright_notice'] ) ) : '';
            $credit_text = isset( $attachment['imagcoma_credit_text'] ) ? sanitize_text_field( wp_unslash( $attachment['imagcoma_credit_text'] ) ) : '';
            $license_url = isset( $attachment['imagcoma_license_url'] ) ? esc_url_raw( wp_unslash( $attachment['imagcoma_license_url'] ) ) : '';
            $acquire_license_url = isset( $attachment['imagcoma_acquire_license_url'] ) ? esc_url_raw( wp_unslash( $attachment['imagcoma_acquire_license_url'] ) ) : '';

            IMAGCOMA_Utils::save_copyright_info( $post['ID'], $copyright_data, $creator, $copyright_notice, $credit_text, $license_url, $acquire_license_url );
        }
        
        $display_copyright = isset( $attachment['imagcoma_display_copyright'] ) ? '1' : '0';
        update_post_meta( $post['ID'], '_imagcoma_display_copyright', $display_copyright );

        return $post;
    }
}