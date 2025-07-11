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
        add_action( 'add_meta_boxes', array( $this, 'add_copyright_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_copyright_data' ) );
        add_action( 'edit_attachment', array( $this, 'save_copyright_data' ) );
        add_action( 'add_attachment', array( $this, 'save_copyright_data' ) );
    }
    
    public function add_copyright_meta_box() {
        $screens = array( 'attachment' );
        
        foreach ( $screens as $screen ) {
            add_meta_box(
                'imagcoma_copyright_box',
                __( 'Copyright Information', 'image-copyright-manager' ),
                array( $this, 'render_copyright_meta_box' ),
                $screen,
                'normal',
                'high'
            );
        }
    }
    
    public function render_copyright_meta_box( $post ) {
        wp_nonce_field( 'imagcoma_save_copyright', 'imagcoma_copyright_nonce' );
        
        $copyright_data = IMAGCOMA_Utils::get_copyright_info( $post->ID );
        $copyright = $copyright_data['copyright'] ?? '';
        $display_copyright = $copyright_data['display_copyright'] ?? false;
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="imagcoma_copyright_field"><?php esc_html_e( 'Copyright Information', 'image-copyright-manager' ); ?></label>
                </th>
                <td>
                    <textarea 
                        id="imagcoma_copyright_field" 
                        name="imagcoma_copyright_field" 
                        class="widefat" 
                        rows="3" 
                        placeholder="<?php esc_attr_e( 'Enter copyright information. You can include links using HTML tags like &lt;a href="https://example.com"&gt;Link Text&lt;/a&gt;', 'image-copyright-manager' ); ?>"
                    ><?php echo esc_textarea( $copyright ); ?></textarea>
                    <p class="description">
                        <?php esc_html_e( 'You can include HTML links and basic formatting in the copyright text.', 'image-copyright-manager' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Display Options', 'image-copyright-manager' ); ?></th>
                <td>
                    <label>
                        <input 
                            type="checkbox" 
                            name="imagcoma_display_copyright" 
                            value="1" 
                            <?php checked( $display_copyright, true ); ?> 
                        />
                        <?php esc_html_e( 'Display copyright text under this image', 'image-copyright-manager' ); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e( 'The display format can be customized in Settings > Image Copyright.', 'image-copyright-manager' ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_copyright_data( $post_id ) {
        if ( ! isset( $_POST['imagcoma_copyright_nonce'] ) || 
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['imagcoma_copyright_nonce'] ) ), 'imagcoma_save_copyright' ) ) {
            return;
        }
        
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if ( isset( $_POST['imagcoma_copyright_field'] ) ) {
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
            
            $copyright_data = wp_kses( wp_unslash( $_POST['imagcoma_copyright_field'] ), $allowed_html );
            IMAGCOMA_Utils::save_copyright_info( $post_id, $copyright_data );
        }
        
        $display_copyright = isset( $_POST['imagcoma_display_copyright'] ) ? '1' : '0';
        update_post_meta( $post_id, '_imagcoma_display_copyright', $display_copyright );
    }
} 