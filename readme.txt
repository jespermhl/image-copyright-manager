=== Image Copyright Manager ===
Contributors: jespermhl
Tags: media, copyright, images, attachments, metadata
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add copyright information to WordPress media files with a custom field and display them using shortcodes.

== Description ==

Image Copyright Manager adds a custom field for copyright information to WordPress media attachments. This allows you to store copyright details for your images and other media files, and display them on your website using shortcodes.

= Features =

* Add copyright information to any media file in WordPress
* Support for HTML links in copyright information
* Custom meta box in the media editor
* Shortcode to display all media with copyright information
* Translation ready
* Secure and follows WordPress coding standards

= Shortcode Usage =

Display all media with copyright information:
`[imagcoma]`

Customize the display:
`[imagcoma orderby="title" order="ASC"]`

Customize heading and texts:
`[imagcoma heading="Image Sources" heading_tag="h2"]`

Fully customized example:
`[imagcoma heading="Photo Credits" heading_tag="h4" no_sources_text="No images found" copyright_label="Source:" view_media_text="View Image"]`

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
2. Click on any media file to edit it
3. You'll see a "Copyright Information" meta box
4. Enter the copyright details and save
5. You can include HTML links using tags like `<a href="https://example.com">Link Text</a>`

= How do I display media with copyright information? =

Use the shortcode `[imagcoma]` anywhere in your posts or pages to display all media that has copyright information.

= Can I customize the shortcode output? =

Yes, you can use various parameters to customize the display:

* Basic sorting: `[imagcoma orderby="title"]`
* Custom heading: `[imagcoma heading="Photo Credits" heading_tag="h2"]`
* Custom texts: `[imagcoma no_sources_text="No images found" copyright_label="Source:" view_media_text="View Image"]`
* Fully customized: `[imagcoma heading="Image Sources" heading_tag="h4" no_sources_text="No sources available" copyright_label="Credits:" view_media_text="View Image"]`

= Can I include links in the copyright information? =

Yes! The copyright field supports HTML links. You can use HTML tags like:
`<a href="https://example.com" target="_blank">Link Text</a>`

This allows you to link to the original source, photographer's website, or any relevant URL.

= Is this plugin translation ready? =

Yes, the plugin is fully translation ready and includes a POT file for creating translations.

== Screenshots ==

1. Copyright information meta box in media editor
2. Shortcode output displaying media with copyright information

== Changelog ==

= 1.1.0 =
* Major performance improvement: Copyright information is now stored in a custom database table for fast and scalable queries.
* Removed all taxonomy-based code and meta_query usage for copyright info.
* Shortcode and admin UI now use the new table structure.
* Improved caching for copyright queries.
* Codebase fully cleaned of legacy taxonomy and meta_query logic.
* Updated translation template (POT) file.
* Various code quality and standards improvements.

= 1.0.7 =
* Changed all function, class, and shortcode prefixes to `imagcoma` for improved uniqueness and consistency.

= 1.0.6 =
* Fixed WordPress coding standards violations for text domain usage
* Replaced constant references with literal strings for translation functions
* Created missing languages directory and POT file for translations
* Resolved ownership verification requirements
* Updated plugin header to include required "Requires at least" and "Requires PHP" headers
* Removed deprecated load_plugin_textdomain() function call
* Ensured proper function prefixing to avoid naming collisions

= 1.0.5 =
* Enhanced shortcode with customizable heading and text parameters
* Changed default heading to "Image Sources"
* Added parameters: heading, heading_tag, no_sources_text, copyright_label, view_media_text
* Improved shortcode flexibility for different use cases
* Updated documentation with new parameter examples
* Added support for HTML links in copyright information
* Changed copyright field from text input to textarea for better link editing
* Added secure HTML sanitization for links and basic formatting

= 1.0.4 =
* Fixed translation file naming convention (WordPress standard)
* Improved translation loading reliability
* Cleaned up debug code

= 1.0.3 =
* Added German translation (de_DE)
* Fixed translation loading issues for existing settings
* Added automatic translation refresh for stored settings
* Added manual translation refresh button in settings
* Improved text domain loading with debug logging

= 1.0.2 =
* Added per-image copyright display option
* Added global settings page (Settings > Image Copyright)
* Added customizable display text format with {copyright} placeholder
* Added customizable CSS class for styling
* Improved user interface with individual image controls
* Enhanced translation support for new features

= 1.0.1 =
* Fixed text domain consistency issues
* Changed function prefixes from wpimc to icm for better code organization
* Updated shortcode from [wpimc] to [icm]
* Improved translation file naming and structure
* Updated meta keys for better consistency

= 1.0.0 =
* Initial release
* Add copyright field to media attachments
* Shortcode functionality
* Translation support
* Security improvements

== Upgrade Notice ==

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