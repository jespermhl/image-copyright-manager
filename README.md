# Image Copyright Manager

![WordPress Requires at least](https://img.shields.io/badge/WordPress-6.4%2B-blue.svg)
![PHP Requires at least](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)
![License](https://img.shields.io/badge/License-GPLv2%2B-green.svg)

Image Copyright Manager adds a custom field for copyright information to WordPress media attachments. This allows you to store copyright details for your images and other media files, and display them on your website using shortcodes.

## Features

*   **Add copyright information** to any media file in WordPress
*   **Support for HTML links** in copyright information
*   **Integrated into Media Modal** and Edit Media screen
*   **Shortcode** to display all media with copyright information
*   **Translation ready**
*   **Secure** and follows WordPress coding standards

## Installation

1.  Upload the plugin files to the `/wp-content/plugins/wp-image-copyright` directory, or install the plugin through the WordPress plugins screen directly.
2.  Activate the plugin through the 'Plugins' screen in WordPress
3.  Use the Settings->Screen to configure the plugin

## Usage

### Adding Copyright Info

1.  Go to Media Library
2.  Click on any media file to edit it (or open the popup)
3.  You'll see a "Copyright Info" field in the sidebar
4.  Enter the copyright details and it saves automatically (or click Update)
5.  You can include HTML links using tags like `<a href="https://example.com">Link Text</a>`

### Displaying Copyright Info

Use the shortcode `[imagcoma]` anywhere in your posts or pages to display all media that has copyright information.

#### Shortcode Examples

**Display all media with copyright information:**
```
[imagcoma]
```

**Customize the display:**
```
[imagcoma orderby="title" order="ASC"]
```

**Customize heading and texts:**
```
[imagcoma heading="Image Sources" heading_tag="h2"]
```

**Fully customized example:**
```
[imagcoma heading="Photo Credits" heading_tag="h4" no_sources_text="No images found" copyright_label="Source:" view_media_text="View Image"]
```

#### Shortcode Parameters

*   `orderby` - Sort by date, title, etc. (default: date)
*   `order` - ASC or DESC (default: DESC)
*   `heading` - Custom heading text (default: "Image Sources")
*   `heading_tag` - HTML heading tag: h1, h2, h3, h4, h5, h6 (default: h3)
*   `no_sources_text` - Text displayed when no sources are found (default: "No image sources with copyright information found.")
*   `copyright_label` - Label for copyright information (default: "Copyright:")
*   `view_media_text` - Text for the "View Media" link (default: "View Media")

## Development

### Build

To build the plugin for production:

```bash
pnpm install
pnpm run build
```

### Deploy

This repository uses GitHub Actions to deploy to the WordPress Plugin Directory SVN.

## Changelog

See [CHANGELOG.txt](CHANGELOG.txt) for the full changelog.
