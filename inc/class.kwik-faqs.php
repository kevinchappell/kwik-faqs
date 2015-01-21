<?php

/**
 * Cor class for Kwik Clients. Creates the custom post type, enqueues
 * styles and scripts and add filter for template redirect
 *
 * @category Core
 * @package  KwikFAQs
 * @author   Kevin Chappell <kevin.b.chappell@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     http://kevin-chappell.com/kwik-faqs/docs/inc/class.kwik-faqs.php/
 * @since    KwikFAQs 1.0
 */

require_once 'class.helpers.php';

class KwikFAQs
{
    static $helpers;

    /**
     * Adds init actions and filters for plugin and deactivation hook
     */
    public function __construct()
    {

        add_action('init', array($this, 'faqs_create_post_type'));
        add_filter('archive_template', array($this, 'archive_template'));
        add_filter('single_template', array($this, 'single_tepmlate'));

        if (is_admin()) {
            $this->admin();
        } else {
            add_action('wp_enqueue_scripts', array($this, 'scripts_and_styles'));
        }

        self::load_widgets();

        // Cleanup on deactivation
        register_deactivation_hook(__FILE__, array($this, '__destruct'));
    }

    /**
     * Currently unused, placeholder for garbage cleanup code.
     */
    public function __destruct()
    {
        // Do garbage cleanup stuff here
    }

    /**
     * Method for adding instance of admin to main class
     * @return Class KwikFAQs Admin
     */
    public function admin()
    {
        if (!isset($this->admin)) {
            include_once __DIR__ . '/class.kwik-faqs-admin.php';
            $this->admin = new KwikFAQs_Admin($this);
        }
        return $this->admin;
    }

    /**
     * Enqueue scripts and styles for the front-end
     * @return Dynamic Styles and scripts added to the head
     */
    public function scripts_and_styles()
    {
        wp_enqueue_script('kwik-faqs-js', K_FAQS_URL . '/js/' . K_FAQS_BASENAME . '.js', array('jquery'));
        wp_enqueue_style('kwik-faqs-css', K_FAQS_URL . '/css/' . K_FAQS_BASENAME . '.css', false, '2015-01-20');
    }

    public function faqs_create_post_type()
    {

        self::create_faqs_taxonomies();
        // new K_FAQS_META();

        register_post_type(
            K_FAQS_CPT,
            array(
                'labels' => array(
                    'name' => __('FAQs', 'kwik'),
                    'all_items' => __('FAQs', 'kwik'),
                    'singular_name' => __('FAQ', 'kwik'),
                    'add_new' => __('Add FAQ', 'kwik'),
                    'add_new_item' => __('Add New FAQ', 'kwik'),
                    'edit_item' => __('Edit FAQ', 'kwik'),
                    'menu_name' => __('FAQs', 'kwik'),
                ),
                'menu_icon' => 'dashicons-lightbulb',
                'menu_position' => 4,

                'supports' => array('title', 'editor', 'thumbnail'),
                'public' => true,
                'exclude_from_search' => false,
                'has_archive' => true,
                'taxonomies' => array('faq_topics'),
                // 'register_meta_box_cb' => 'add_faqs_metabox',
                'rewrite' => array('slug' => K_FAQS_CPT),
                'query_var' => true,
            )
        );

        add_image_size('faq_logo', 240, 240, false);
        flush_rewrite_rules(false);
    }

    public function create_faqs_taxonomies()
    {

        $faq_topics_labels = array(
            'name' => _x('Topic', 'taxonomy general name'),
            'singular_name' => _x('Topic', 'taxonomy singular name'),
            'search_items' => __('Search Topics'),
            'all_items' => __('All Topics'),
            'edit_item' => __('Edit Topic'),
            'update_item' => __('Update Topic'),
            'add_new_item' => __('Add New Topic'),
            'new_item_name' => __('New Topic'),
        );

        register_taxonomy('faq_topics', array(K_FAQS_CPT),
            array(
                'hierarchical' => false,
                'labels' => $faq_topics_labels,
                'show_ui' => true,
                'query_var' => true,
                'show_admin_column' => true,
                'rewrite' => array('slug' => 'faq-topic'),
              )
        );

    }

    public function archive_template($archive)
    {
        global $wp_query, $post;

        /* Checks for single template by post type */
        if ($post->post_type === K_FAQS_CPT) {
            if (file_exists(K_FAQS_PATH . '/template/archive-' . K_FAQS_CPT . '.php')) {
                return K_FAQS_PATH . '/template/archive-' . K_FAQS_CPT . '.php';
            }

        }
        return $archive;
    }

    public function single_template($single)
    {
        global $wp_query, $post;

        /* Checks for single template by post type */
        if ($post->post_type === K_FAQS_CPT) {
            if (file_exists(K_FAQS_PATH . '/template/single-' . K_FAQS_CPT . '.php')) {
                return K_FAQS_PATH . '/template/single-' . K_FAQS_CPT . '.php';
            }

        }
        return $single;
    }

    public function load_widgets()
    {
        foreach (glob(K_FAQS_PATH . "/widgets/*.php") as $inc_filename) {
            include $inc_filename;
        }
    }

}// / Class KwikFAQs

// Singleton
function kwik_faqs()
{
    global $kwik_faqs;
    if (!$kwik_faqs) {
        $kwik_faqs = new KwikFAQs();
    }
    return $kwik_faqs;
}
