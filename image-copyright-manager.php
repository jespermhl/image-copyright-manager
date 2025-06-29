<?php
/**
 * Plugin Name: Image Copyright Manager
 * Plugin URI:  https://mahelwebdesign.com/image-copyright-manager/
 * Description: Adds a custom field for copyright information to WordPress media.
 * Version:     1.0.5
 * Author:      Mahel Webdesign
 * Author URI:  https://mahelwebdesign.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: image-copyright-manager
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function icm_load_textdomain() {
    load_plugin_textdomain( 'image-copyright-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'icm_load_textdomain' );

function icm_add_custom_box() {
    $screens = [ 'attachment' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
			'icm_box_id',
			__( 'Copyright Information', 'image-copyright-manager' ),
			'icm_custom_box_html',
			$screen
		);
    }
}

add_action( 'add_meta_boxes', 'icm_add_custom_box' );

function icm_custom_box_html( $post ) {
	wp_nonce_field( 'icm_save_meta', 'icm_meta_nonce' );
	
	$value = get_post_meta( $post->ID, '_icm_copyright', true );
	$display_copyright = get_post_meta( $post->ID, '_icm_display_copyright', true );
	?>
	<label for="icm_field"><?php esc_html_e( 'Copyright Information', 'image-copyright-manager' ); ?></label>
	<textarea id="icm_field" name="icm_field" class="widefat" rows="3" placeholder="<?php esc_attr_e( 'Enter copyright information. You can include links using HTML tags like &lt;a href="https://example.com"&gt;Link Text&lt;/a&gt;', 'image-copyright-manager' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
	
	<br><br>
	
	<label>
		<input type="checkbox" name="icm_display_copyright" value="1" <?php checked( $display_copyright, '1' ); ?> />
		<?php esc_html_e( 'Display copyright text under this image', 'image-copyright-manager' ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'The display format can be customized in Settings > Image Copyright. You can include HTML links in the copyright field.', 'image-copyright-manager' ); ?></p>
	<?php
}

function icm_save_postdata( $post_id ) {
	if ( ! isset( $_POST['icm_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['icm_meta_nonce'] ) ), 'icm_save_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( array_key_exists( 'icm_field', $_POST ) ) {
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
		
		$copyright_data = wp_kses( wp_unslash( $_POST['icm_field'] ), $allowed_html );
		update_post_meta( $post_id, '_icm_copyright', $copyright_data );
	}
	
	$display_copyright = isset( $_POST['icm_display_copyright'] ) ? '1' : '0';
	update_post_meta( $post_id, '_icm_display_copyright', $display_copyright );
}

function icm_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'orderby' => 'date',
        'order' => 'DESC',
        'heading' => __( 'Image Sources', 'image-copyright-manager' ),
        'heading_tag' => 'h3',
        'no_sources_text' => __( 'No image sources with copyright information found.', 'image-copyright-manager' ),
        'copyright_label' => __( 'Copyright:', 'image-copyright-manager' ),
        'view_media_text' => __( 'View Media', 'image-copyright-manager' )
    ), $atts );

    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'meta_query' => array(
            array(
                'key' => '_icm_copyright',
                'value' => '',
                'compare' => '!=',
                'type' => 'CHAR'
            )
        ),
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false
    );

    $query = new WP_Query( $args );
    
    if ( ! $query->have_posts() ) {
        return '<p>' . esc_html( $atts['no_sources_text'] ) . '</p>';
    }

    $output = '<div class="icm-media-list">';
    $output .= '<' . esc_attr( $atts['heading_tag'] ) . '>' . esc_html( $atts['heading'] ) . '</' . esc_attr( $atts['heading_tag'] ) . '>';
    $output .= '<ul>';

    while ( $query->have_posts() ) {
        $query->the_post();
        $attachment_id = get_the_ID();
        $copyright = get_post_meta( $attachment_id, '_icm_copyright', true );
        $attachment_url = wp_get_attachment_url( $attachment_id );
        $attachment_title = get_the_title( $attachment_id );
        
        $output .= '<li>';
        $output .= '<strong>' . esc_html( $attachment_title ) . '</strong><br>';
        $output .= '<em>' . esc_html( $atts['copyright_label'] ) . ' ' . wp_kses_post( $copyright ) . '</em><br>';
        if ( $attachment_url ) {
            $output .= '<a href="' . esc_url( $attachment_url ) . '" target="_blank">' . esc_html( $atts['view_media_text'] ) . '</a>';
        }
        $output .= '</li>';
    }

    $output .= '</ul>';
    $output .= '</div>';

    wp_reset_postdata();

    return $output;
}

add_shortcode( 'icm', 'icm_shortcode' );
add_action( 'save_post', 'icm_save_postdata' );
add_action( 'edit_attachment', 'icm_save_postdata' );
add_action( 'add_attachment', 'icm_save_postdata' );

function icm_get_settings() {
	$settings = get_option( 'icm_settings', array() );
	
	$defaults = array(
		'display_text' => __( 'Copyright: {copyright}', 'image-copyright-manager' ),
		'css_class' => 'icm-copyright-text'
	);
	
	$settings = wp_parse_args( $settings, $defaults );
	
	return $settings;
}

function icm_add_admin_menu() {
	add_options_page(
		__( 'Image Copyright Manager', 'image-copyright-manager' ),
		__( 'Image Copyright', 'image-copyright-manager' ),
		'manage_options',
		'image-copyright-manager',
		'icm_settings_page'
	);
}
add_action( 'admin_menu', 'icm_add_admin_menu' );

function icm_settings_page() {
	if ( isset( $_POST['icm_save_settings'] ) && isset( $_POST['icm_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['icm_settings_nonce'] ) ), 'icm_save_settings' ) ) {
		$settings = array(
			'display_text' => isset( $_POST['display_text'] ) ? sanitize_text_field( wp_unslash( $_POST['display_text'] ) ) : '',
			'css_class' => isset( $_POST['css_class'] ) ? sanitize_html_class( wp_unslash( $_POST['css_class'] ) ) : ''
		);
		update_option( 'icm_settings', $settings );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully!', 'image-copyright-manager' ) . '</p></div>';
	}
	
	$settings = icm_get_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Image Copyright Manager Settings', 'image-copyright-manager' ); ?></h1>
		<form method="post" action="">
			<?php wp_nonce_field( 'icm_save_settings', 'icm_settings_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Display Text Format', 'image-copyright-manager' ); ?></th>
					<td>
						<input type="text" name="display_text" value="<?php echo esc_attr( $settings['display_text'] ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Use {copyright} as placeholder for the actual copyright text', 'image-copyright-manager' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'CSS Class', 'image-copyright-manager' ); ?></th>
					<td>
						<input type="text" name="css_class" value="<?php echo esc_attr( $settings['css_class'] ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'CSS class for styling the copyright text', 'image-copyright-manager' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" name="icm_save_settings" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'image-copyright-manager' ); ?>" />
			</p>
		</form>
	</div>
	<?php
}

function icm_auto_display_copyright( $content ) {
	$settings = icm_get_settings();
	
	$pattern = '/<img[^>]+>/i';
	$content = preg_replace_callback( $pattern, function( $matches ) use ( $settings ) {
		$img_tag = $matches[0];
		
		$attachment_id = icm_get_attachment_id_from_img_tag( $img_tag );
		
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
add_filter( 'the_content', 'icm_auto_display_copyright' );

function icm_get_attachment_id_from_img_tag( $img_tag ) {
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

function icm_add_copyright_styles() {
	$settings = icm_get_settings();
	?>
	<style>
		.<?php echo esc_attr( $settings['css_class'] ); ?> {
			font-size: 0.9em;
			color: #666;
			margin: 5px 0;
			font-style: italic;
			text-align: center;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'icm_add_copyright_styles' );