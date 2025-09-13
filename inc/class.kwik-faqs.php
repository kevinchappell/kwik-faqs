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
    private ?KwikFAQs_Admin $admin_instance = null;

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
            $this->load_admin();
        } else {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
        }

        add_action( 'init', array( $this, 'load_widgets' ) );

        // Load the FAQ filter block
        $this->load_blocks();

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
     * Initialize admin functionality on admin_init
     */
    public function init_admin(): void
    {
        if ( is_admin() ) {
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log('Kwik FAQs: init_admin called, loading admin class');
            }
            $this->load_admin();
        }
    }

    /**
     * Get admin instance
     *
     * @return KwikFAQs_Admin Admin instance
     */
    public function admin(): KwikFAQs_Admin
    {
        if (!isset($this->admin_instance)) {
            $this->load_admin();
        }
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
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('Kwik FAQs: create_post_type called');
        }
        
        // new K_FAQS_META();

        $result = register_post_type(
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
                'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
                'public' => true,
                'exclude_from_search' => false,
                'has_archive' => true,
                'rewrite' => array( 'slug' => KWIK_FAQS_CPT ),
                'query_var' => true,
                'show_in_rest' => true, // Add REST API support
            )
        );
        
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            if (is_wp_error($result)) {
                error_log('Kwik FAQs: Post type registration failed: ' . $result->get_error_message());
            } else {
                error_log('Kwik FAQs: Post type registration successful');
            }
        }

        add_image_size( 'faq_logo', 240, 240, false );
        flush_rewrite_rules( false );
    }

    /**
     * Override archive template
     *
     * @param string $archive Archive template path.
     * @return string Modified archive template path.
     */
    public function archive_template( string $archive ): string
    {
        // Check if we're on the FAQ archive page
        if ( is_post_type_archive( KWIK_FAQS_CPT ) ) {
            // First check if theme has an override
            $theme_template = locate_template( 'archive-' . KWIK_FAQS_CPT . '.php' );
            if ( $theme_template ) {
                return $theme_template;
            }
            
            // Fall back to plugin template
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

    /**
     * Load Gutenberg blocks
     */
    public function load_blocks(): void
    {
        // Load the FAQ filter block
        require_once KWIK_FAQS_PATH . 'inc/faq-filter-block.php';
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
