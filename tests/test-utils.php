<?php
/**
 * Class UtilsTest
 *
 * @package Image_Copyright_Manager
 */

/**
 * Unit tests for IMAGCOMA_Utils.
 */
class UtilsTest extends WP_UnitTestCase {

	/**
	 * Test extracting attachment ID from img tag.
	 */
	public function test_get_attachment_id_from_img_tag() {
		$img_tag = '<img src="test.jpg" class="wp-image-123">';
		$this->assertEquals( 123, IMAGCOMA_Utils::get_attachment_id_from_img_tag( $img_tag ) );

		$img_tag = '<img src="test.jpg" data-attachment-id="456">';
		$this->assertEquals( 456, IMAGCOMA_Utils::get_attachment_id_from_img_tag( $img_tag ) );

		$img_tag = '<img src="no-id.jpg">';
		$this->assertFalse( IMAGCOMA_Utils::get_attachment_id_from_img_tag( $img_tag ) );
	}

	/**
	 * Test formatting copyright text.
	 */
	public function test_format_copyright_text() {
		$copyright = 'John Doe';
		$settings = array( 'display_text' => 'Copyright: {copyright}' );
		$expected = 'Copyright: John Doe';
		
		$this->assertEquals( $expected, IMAGCOMA_Utils::format_copyright_text( $copyright, $settings ) );
	}

	/**
	 * Test sanitizing copyright HTML.
	 */
	public function test_sanitize_copyright_html() {
		$dirty_html = '<script>alert("xss");</script><strong>Valid</strong><a href="http://example.com">Link</a>';
		$clean_html = IMAGCOMA_Utils::sanitize_copyright_html( $dirty_html );
		
		$this->assertStringNotContainsString( '<script>', $clean_html );
		$this->assertStringContainsString( '<strong>Valid</strong>', $clean_html );
		$this->assertStringContainsString( '<a href="http://example.com">Link</a>', $clean_html );
	}
}
