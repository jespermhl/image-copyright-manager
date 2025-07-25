# Image Copyright Manager

A WordPress plugin that adds copyright information management to media attachments with automatic display capabilities.

---

**New in 1.1.3:**
- Added CSS toggle setting in admin panel (Settings → Image Copyright)
- CSS is now conditionally loaded based on user preference

---

## Description

Image Copyright Manager allows you to add copyright information to your WordPress media files and automatically display this information under images on your website. The plugin provides a user-friendly interface for managing copyright data and includes customizable display options.

## Features

- **Copyright Field**: Add copyright information to any media attachment
- **HTML Support**: Include links and basic HTML formatting in copyright text
- **Auto-Display**: Automatically show copyright information under images
- **Customizable Format**: Configure display text format and CSS styling
- **CSS Styling Control**: Activate or deactivate custom CSS styling for copyright information (since 1.1.3)
- **Shortcode Support**: Display all copyrighted images using `[imagcoma]` shortcode
- **Performance**: Copyright info is now stored in a custom table for fast queries (since 1.1.0)
- **Improved Caching**: Faster display of copyright lists (since 1.1.0)
- **Multilingual**: Translation-ready with German translations included
- **Settings Page**: Easy configuration through WordPress admin

## Installation

1. Upload the plugin files to the `/wp-content/plugins/image-copyright-manager/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Image Copyright to configure display options

## Usage

### Adding Copyright Information

1. Go to Media Library
2. Edit any image
3. Scroll down to find the "Copyright Information" meta box
4. Enter your copyright text (HTML links are supported)
5. Check "Display copyright text under this image" if you want it to show automatically
6. Update the media

### Display Options

The plugin automatically displays copyright information under images when:
- Copyright text is added to the media
- "Display copyright text under this image" is checked
- The image is embedded in post content

### Shortcode

Use the `[imagcoma]` shortcode to display all images with copyright information:

```
[imagcoma]
```

Shortcode parameters:
- `orderby`: Sort order (date, title, etc.)
- `order`: ASC or DESC
- `heading`: Custom heading text
- `heading_tag`: HTML tag for heading (h1, h2, h3, etc.)
- `no_sources_text`: Text when no copyrighted images found
- `copyright_label`: Label for copyright text
- `view_media_text`: Text for media link

Example:
```
[imagcoma orderby="title" order="ASC" heading="Image Credits"]
```

### Customization

Go to Settings > Image Copyright to customize:
- Display text format (use `{copyright}` placeholder)
- CSS class for styling

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Support

For support and feature requests, visit [Mahel Webdesign](https://mahelwebdesign.com/image-copyright-manager/).

## License

This plugin is licensed under the GPL v2 or later.

## Author

**Mahel Webdesign**
- Website: https://mahelwebdesign.com/
- Plugin URL: https://mahelwebdesign.com/image-copyright-manager/ 