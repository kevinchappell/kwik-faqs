<?php
/**
 * Plugin Name: Kwik FAQs
 * Description: A simple FAQs plugin for WordPress
 * Author: Kevin Chappell
 * Version: 1.0.0
 * Text Domain: kwik-faqs
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants with improved naming and WordPress standards
define( 'KWIK_FAQS_VERSION', '1.0.0' );
define( 'KWIK_FAQS_BASENAME', plugin_basename( __FILE__ ) );
define( 'KWIK_FAQS_URL', plugin_dir_url( __FILE__ ) );
define( 'KWIK_FAQS_PATH', plugin_dir_path( __FILE__ ) );
define( 'KWIK_FAQS_CPT', 'faqs' );
define( 'KWIK_FAQS_SETTINGS', 'kwik_faqs_settings' );

// Load the core plugin class
require_once plugin_dir_path( __FILE__ ) . 'inc/class.kwik-faqs.php';

// Initialize the plugin
kwik_faqs_init();
