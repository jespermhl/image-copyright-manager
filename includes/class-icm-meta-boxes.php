<?php
/**
 * Meta boxes functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ICM_Meta_Boxes {
    
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
                'icm_copyright_box',
                __( 'Copyright Information', ICM_Core::TEXT_DOMAIN ),
                array( $this, 'render_copyright_meta_box' ),
                $screen,
                'normal',
                'high'
            );
        }
    }
    
    public function render_copyright_meta_box( $post ) {
        wp_nonce_field( 'icm_save_copyright', 'icm_copyright_nonce' );
        
        $copyright = get_post_meta( $post->ID, '_icm_copyright', true );
        $display_copyright = get_post_meta( $post->ID, '_icm_display_copyright', true );
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="icm_copyright_field"><?php esc_html_e( 'Copyright Information', ICM_Core::TEXT_DOMAIN ); ?></label>
                </th>
                <td>
                    <textarea 
                        id="icm_copyright_field" 
                        name="icm_copyright_field" 
                        class="widefat" 
                        rows="3" 
                        placeholder="<?php esc_attr_e( 'Enter copyright information. You can include links using HTML tags like &lt;a href="https://example.com"&gt;Link Text&lt;/a&gt;', ICM_Core::TEXT_DOMAIN ); ?>"
                    ><?php echo esc_textarea( $copyright ); ?></textarea>
                    <p class="description">
                        <?php esc_html_e( 'You can include HTML links and basic formatting in the copyright text.', ICM_Core::TEXT_DOMAIN ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Display Options', ICM_Core::TEXT_DOMAIN ); ?></th>
                <td>
                    <label>
                        <input 
                            type="checkbox" 
                            name="icm_display_copyright" 
                            value="1" 
                            <?php checked( $display_copyright, '1' ); ?> 
                        />
                        <?php esc_html_e( 'Display copyright text under this image', ICM_Core::TEXT_DOMAIN ); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e( 'The display format can be customized in Settings > Image Copyright.', ICM_Core::TEXT_DOMAIN ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_copyright_data( $post_id ) {
        if ( ! isset( $_POST['icm_copyright_nonce'] ) || 
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['icm_copyright_nonce'] ) ), 'icm_save_copyright' ) ) {
            return;
        }
        
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if ( isset( $_POST['icm_copyright_field'] ) ) {
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
            
            $copyright_data = wp_kses( wp_unslash( $_POST['icm_copyright_field'] ), $allowed_html );
            update_post_meta( $post_id, '_icm_copyright', $copyright_data );
        }
        
        $display_copyright = isset( $_POST['icm_display_copyright'] ) ? '1' : '0';
        update_post_meta( $post_id, '_icm_display_copyright', $display_copyright );
    }
} 