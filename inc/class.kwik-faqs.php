<?php
/**
 * Core class for Kwik FAQs plugin.
 * Creates the custom post type, enqueues styles and scripts,
 * and handles template redirects.
 *
 * @package   KwikFAQs
 * @author    Kevin Chappell <kevin.b.chappell@gmail.com>
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
 * @since     KwikFAQs 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once KWIK_FAQS_PATH . 'inc/class.helpers.php';

class KwikFAQs
{
    /**
     * @var KwikFAQs_Admin Admin instance
     */
    private KwikFAQs_Admin $admin_instance;

    /**
     * @var K_FAQS_HELPERS Helpers instance
     */
    static $helpers;

    /**
     * Initialize the plugin and set up hooks
     */
    public function __construct()
    {
        add_action( 'init', array( $this, 'create_post_type' ) );
        add_filter( 'archive_template', array( $this, 'archive_template' ) );
        add_filter( 'single_template', array( $this, 'single_template' ) );

        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'load_admin' ) );
        } else {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
        }

        add_action( 'init', array( $this, 'load_widgets' ) );

        // Cleanup on deactivation
        register_deactivation_hook( KWIK_FAQS_PATH . 'kwik-faqs.php', array( $this, 'deactivate' ) );
    }

    /**
     * Currently unused, placeholder for garbage cleanup code.
     */
    public function __destruct()
    {
        // Do garbage cleanup stuff here
    }

    /**
     * Load admin functionality
     */
    public function load_admin(): void
    {
        if ( ! isset( $this->admin_instance ) ) {
            require_once KWIK_FAQS_PATH . 'inc/class.kwik-faqs-admin.php';
            $this->admin_instance = new KwikFAQs_Admin( $this );
        }
    }

    /**
     * Get admin instance
     *
     * @return KwikFAQs_Admin Admin instance
     */
    public function admin(): KwikFAQs_Admin
    {
        return $this->admin_instance;
    }

    /**
     * Enqueue scripts and styles for the front-end
     */
    public function enqueue_scripts_and_styles(): void
    {
        wp_enqueue_script(
            'kwik-faqs-js',
            KWIK_FAQS_URL . 'js/kwik-faqs.js',
            array( 'jquery' ),
            KWIK_FAQS_VERSION,
            true
        );
        wp_enqueue_style(
            'kwik-faqs-css',
            KWIK_FAQS_URL . 'css/kwik-faqs.css',
            array(),
            KWIK_FAQS_VERSION
        );
    }

    /**
     * Deactivation hook
     */
    public function deactivate(): void
    {
        flush_rewrite_rules();
    }

    /**
     * Create the FAQ custom post type
     */
    public function create_post_type(): void
    {
        $this->create_faqs_taxonomies();
        // new K_FAQS_META();

        register_post_type(
            KWIK_FAQS_CPT,
            array(
                'labels' => array(
                    'name' => __( 'FAQs', 'kwik' ),
                    'all_items' => __( 'FAQs', 'kwik' ),
                    'singular_name' => __( 'FAQ', 'kwik' ),
                    'add_new' => __( 'Add FAQ', 'kwik' ),
                    'add_new_item' => __( 'Add New FAQ', 'kwik' ),
                    'edit_item' => __( 'Edit FAQ', 'kwik' ),
                    'menu_name' => __( 'FAQs', 'kwik' ),
                ),
                'menu_icon' => 'dashicons-lightbulb',
                'menu_position' => 4,
                'supports' => array( 'title', 'editor', 'thumbnail' ),
                'public' => true,
                'exclude_from_search' => false,
                'has_archive' => true,
                'taxonomies' => array( 'faq_topics' ),
                'rewrite' => array( 'slug' => KWIK_FAQS_CPT ),
                'query_var' => true,
                'show_in_rest' => true, // Add REST API support
            )
        );

        add_image_size( 'faq_logo', 240, 240, false );
        flush_rewrite_rules( false );
    }

    /**
     * Create FAQ taxonomies
     */
    public function create_faqs_taxonomies(): void
    {
        $faq_topics_labels = array(
            'name' => _x( 'Topic', 'taxonomy general name', 'kwik' ),
            'singular_name' => _x( 'Topic', 'taxonomy singular name', 'kwik' ),
            'search_items' => __( 'Search Topics', 'kwik' ),
            'all_items' => __( 'All Topics', 'kwik' ),
            'edit_item' => __( 'Edit Topic', 'kwik' ),
            'update_item' => __( 'Update Topic', 'kwik' ),
            'add_new_item' => __( 'Add New Topic', 'kwik' ),
            'new_item_name' => __( 'New Topic', 'kwik' ),
        );

        register_taxonomy(
            'faq_topics',
            array( KWIK_FAQS_CPT ),
            array(
                'hierarchical' => false,
                'labels' => $faq_topics_labels,
                'show_ui' => true,
                'query_var' => true,
                'show_admin_column' => true,
                'rewrite' => array( 'slug' => 'faq-topic' ),
                'show_in_rest' => true, // Add REST API support
            )
        );
    }

    /**
     * Override archive template
     *
     * @param string $archive Archive template path.
     * @return string Modified archive template path.
     */
    public function archive_template( string $archive ): string
    {
        global $post;

        if ( is_a( $post, 'WP_Post' ) && $post->post_type === KWIK_FAQS_CPT ) {
            $template = KWIK_FAQS_PATH . 'template/archive-' . KWIK_FAQS_CPT . '.php';
            if ( file_exists( $template ) ) {
                return $template;
            }
        }
        return $archive;
    }

    /**
     * Override single template
     *
     * @param string $single Single template path.
     * @return string Modified single template path.
     */
    public function single_template( string $single ): string
    {
        global $post;

        if ( is_a( $post, 'WP_Post' ) && $post->post_type === KWIK_FAQS_CPT ) {
            $template = KWIK_FAQS_PATH . 'template/single-' . KWIK_FAQS_CPT . '.php';
            if ( file_exists( $template ) ) {
                return $template;
            }
        }
        return $single;
    }

    /**
     * Load widget files
     */
    public function load_widgets(): void
    {
        $widget_files = glob( KWIK_FAQS_PATH . 'widgets/*.php' );
        if ( $widget_files ) {
            foreach ( $widget_files as $file ) {
                include_once $file;
            }
        }
    }
}

// Singleton initialization
function kwik_faqs_init(): KwikFAQs
{
    static $instance = null;
    if ( null === $instance ) {
        $instance = new KwikFAQs();
    }
    return $instance;
}
