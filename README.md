# Kwik FAQs #
Easily add an interactive FAQs page to your website.

## Description ##
Kwik FAQs is a WordPress plugin that allows you to create and manage Frequently Asked Questions (FAQs) with an easy-to-use interface.

## Features ##
- Custom FAQ post type
- FAQ topics taxonomy
- Admin interface for managing FAQs
- Shortcode support
- Widget for displaying FAQs
- REST API support
- Gutenberg block editor compatible
- Import FAQs from JSON files

## Requirements ##
- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Installation ##
1. Upload the `kwik-faqs` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create FAQ posts and assign them to topics
4. Use the widget or shortcodes to display FAQs on your site

## Usage ##
1. Install and activate plugin
2. Add FAQ widget to your website
3. Users submit questions to your site through the FAQ widget
4. Answer the questions and publish.
5. To import FAQs from a JSON file, go to FAQs > Import in the WordPress admin and upload your file.

## Security ##
- All user input is sanitized and validated using WordPress core functions
- Nonce verification on all forms and AJAX requests
- Capability checks (`manage_options`) on all admin functions
- No sensitive data stored in code (no API keys, credentials, or tokens)
- Prepared statements for database queries via `$wpdb`
- Output properly escaped using `esc_html()`, `esc_attr()`, `esc_url()`, and `wp_kses_post()`
- File upload restrictions and validation for import functionality
- Follows WordPress Security Best Practices

### Reporting Security Issues
If you discover a security vulnerability, please report it responsibly:
- **Do not** create a public GitHub issue
- Email the maintainer directly (see plugin author information)
- Allow reasonable time for the issue to be addressed before public disclosure

## Changelog ##

### 1.2.0 (2025-10-19) ###
**Security & Documentation Release - Public Release Ready**
- Complete modernization for WordPress 6.8+ and PHP 8.3+
- Added REST API support for modern integration
- Comprehensive security improvements:
  - Input validation and sanitization
  - Nonce verification on all forms
  - Capability checks on admin functions
  - Output escaping to prevent XSS
  - No hardcoded credentials or sensitive data
- Updated admin interface with improved UX
- Enhanced type safety with PHP 7.4+ type hints
- Removed all deprecated WordPress functions
- Added proper error handling and validation
- Improved widget functionality
- Added FAQ import functionality from JSON files
- Added comprehensive documentation (SECURITY.md, CONTRIBUTING.md)
- Production-ready code following WordPress coding standards

### 1.1.0 (2025-01-05) ###
- Added FAQ import functionality from JSON files
- Added drag-and-drop FAQ ordering
- Improved admin interface
- Added REST API support

### 1.0.0 (2024-12-15) ###
- First stable release
- Custom FAQ post type
- FAQ topics taxonomy
- Widget support
- Gutenberg compatible

### 0.3 ###
- Initial development release

## Credits ##
Developed by Kevin Chappell
