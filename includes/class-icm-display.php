<?php
/**
 * Display functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class ICM_Display {
    
    public function __construct() {
        add_filter( 'the_content', array( $this, 'auto_display_copyright' ) );
        add_action( 'wp_head', array( $this, 'add_copyright_styles' ) );
    }
    
    public function auto_display_copyright( $content ) {
        $settings = ICM_Core::get_settings();
        
        $pattern = '/<img[^>]+>/i';
        $content = preg_replace_callback( $pattern, function( $matches ) use ( $settings ) {
            $img_tag = $matches[0];
            
            $attachment_id = ICM_Utils::get_attachment_id_from_img_tag( $img_tag );
            
            if ( ! $attachment_id ) {
                return $img_tag;
            }
            
            $display_copyright = get_post_meta( $attachment_id, '_icm_display_copyright', true );
            if ( $display_copyright !== '1' ) {
                return $img_tag;
            }
            
            $copyright = get_post_meta( $attachment_id, '_icm_copyright', true );
            
            if ( empty( $copyright ) ) {
                return $img_tag;
            }
            
            $copyright_text = str_replace( '{copyright}', $copyright, $settings['display_text'] );
            $copyright_html = '<div class="' . esc_attr( $settings['css_class'] ) . '">' . wp_kses_post( $copyright_text ) . '</div>';
            
            return $img_tag . $copyright_html;
        }, $content );
        
        return $content;
    }
    
    public function add_copyright_styles() {
        $settings = ICM_Core::get_settings();
        ?>
        <style>
            .<?php echo esc_attr( $settings['css_class'] ); ?> {
                font-size: 0.9em;
                color: #666;
                margin: 5px 0;
                font-style: italic;
                text-align: center;
            }
            
            .icm-media-list {
                margin: 20px 0;
            }
            
            .icm-media-list ul {
                list-style: none;
                padding: 0;
            }
            
            .icm-media-list li {
                margin-bottom: 15px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f9f9f9;
            }
        </style>
        <?php
    }
} 