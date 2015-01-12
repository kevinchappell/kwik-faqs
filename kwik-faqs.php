<?php
/*
Plugin Name: Kwik FAQs
Plugin URI: http://kevin-chappell.com/kwik-faqs
Description: Easily add an interactive FAQs page to your website.
Author: Kevin Chappell
Version: .1.1
Author URI: http://kevin-chappell.com
 */


define('K_FAQS_BASENAME', basename(dirname( __FILE__ )));
define('K_FAQS_SETTINGS', preg_replace('/-/', '_', K_FAQS_BASENAME).'_settings');
define('K_FAQS_URL', plugins_url('', __FILE__));
define('K_FAQS_PATH', dirname( __FILE__ ) );
define('K_FAQS_CPT', 'faqs' );


// Load the core.
require_once __DIR__ . '/inc/class.kwik-faqs.php';

kwik_faqs();
