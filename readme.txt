=== Image Copyright Manager ===
Contributors: jespermhl
Tags: media, copyright, images, attachments, metadata
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add copyright information to WordPress media files with a custom field and display them using shortcodes.

== Description ==

Image Copyright Manager adds a custom field for copyright information to WordPress media attachments. This allows you to store copyright details for your images and other media files, and display them on your website using shortcodes.

= Features =

* Add copyright information to any media file in WordPress
* Support for HTML links in copyright information
* Integrated into Media Modal and Edit Media screen
* Shortcode to display all media with copyright information
* Translation ready
* Secure and follows WordPress coding standards

= Shortcode Usage =

Display all media with copyright information:
<pre>[imagcoma]</pre>

Customize the display:
<pre>[imagcoma orderby="title" order="ASC"]</pre>

Customize heading and texts:
<pre>[imagcoma heading="Image Sources" heading_tag="h2"]</pre>

Fully customized example:
<pre>[imagcoma heading="Photo Credits" heading_tag="h4" no_sources_text="No images found" copyright_label="Source:" view_media_text="View Image"]</pre>

= Shortcode Parameters =

* `orderby` - Sort by date, title, etc. (default: date)
* `order` - ASC or DESC (default: DESC)
* `heading` - Custom heading text (default: "Image Sources")
* `heading_tag` - HTML heading tag: h1, h2, h3, h4, h5, h6 (default: h3)
* `no_sources_text` - Text displayed when no sources are found (default: "No image sources with copyright information found.")
* `copyright_label` - Label for copyright information (default: "Copyright:")
* `view_media_text` - Text for the "View Media" link (default: "View Media")

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-image-copyright` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Screen to configure the plugin

== Frequently Asked Questions ==

= How do I add copyright information to my media files? =

1. Go to Media Library
2. Click on any media file to edit it (or open the popup)
3. You'll see a "Copyright Info" field in the sidebar
4. Enter the copyright details and it saves automatically (or click Update)
5. You can include HTML links using tags like `<a href="https://example.com">Link Text</a>`

= How do I display media with copyright information? =

Use the shortcode `[imagcoma]` anywhere in your posts or pages to display all media that has copyright information. For details on customizing the output, see the Shortcode Usage and Shortcode Parameters sections above.

= Can I customize the shortcode output? =

Yes, you can use various parameters to customize the display. See the Shortcode Usage and Shortcode Parameters sections above for all available options.

= Can I include links in the copyright information? =

Yes! The copyright field supports HTML links, so you can link to the original source, photographer's website, or any relevant URL.

= Is this plugin translation ready? =

Yes, the plugin is fully translation ready and includes a POT file for creating translations.

== Screenshots ==

1. Copyright information field in media modal
2. Shortcode output displaying media with copyright information

== Changelog ==

= 1.2.1 =
- Fixed build process to exclude development files from the production zip.

= 1.2.0 =
- Moved copyright input field to Media Modal interface
- Improved performance with object caching
- Improved robustness with DOMDocument HTML parsing
- Added build process

= 1.1.3 =
- Added CSS toggle setting in admin panel (Settings → Image Copyright)
- Added ability to enable/disable custom CSS styling for copyright information
- Added checkbox control for "Enable CSS Styling" option
- CSS is now conditionally loaded based on user preference
- When CSS is disabled, copyright information displays with browser default styling
- Improved user control over plugin styling behavior

Please refer to the CHANGELOG.txt file for the complete changelog.

== Upgrade Notice ==

= 1.2.1 =
Fixed build process to exclude development files.

= 1.2.0 =
Major update: Moved copyright field to Media Modal, improved performance, and robustness.

= 1.1.3 =
This update adds a CSS toggle setting and improves user control over plugin styling behavior.

= 1.1.2 =
This update includes bug fixes and minor improvements. The translation template has been updated for better internationalization support.

= 1.1.1 =
This update includes bug fixes and minor improvements. The translation template has been updated for better internationalization support.

= 1.1.0 =
This update migrates copyright information to a custom database table for much better performance and scalability. All old taxonomy code has been removed. Please back up your database before upgrading.

= 1.0.6 =
This update addresses WordPress Plugin Directory submission requirements and resolves ownership verification issues. The plugin now includes all required headers and follows WordPress coding standards for directory submission.

= 1.0.5 =
This update enhances the shortcode with new customization options. The default heading is now "Image Sources" and you can customize all text elements using new parameters like heading, heading_tag, no_sources_text, copyright_label, and view_media_text.

= 1.0.4 =
This update fixes translation file naming to follow WordPress standards, ensuring proper translation loading across all locales.

= 1.0.3 =
This update adds German translation support and fixes translation loading issues. The plugin now automatically updates stored settings when the language changes and includes a manual refresh option in the settings page.

= 1.0.2 =
This update adds a new per-image copyright display feature with global settings. Users can now choose to display copyright text under individual images and customize the display format globally through Settings > Image Copyright.

= 1.0.1 =
This update includes important text domain fixes and function prefix changes. The shortcode has been updated from [wpimc] to [icm]. Please update any existing shortcodes in your content.

= 1.0.0 =
Initial release of Image Copyright Manager plugin. 