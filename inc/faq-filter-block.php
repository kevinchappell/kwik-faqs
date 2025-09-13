<?php
/**
 * FAQ Filter Block
 * Creates a Gutenberg block for FAQ filtering functionality
 * 
 * @package KwikFAQs
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the FAQ Filter block
 */
function kwik_faqs_register_filter_block() {
    // Only register if we're in a WordPress environment with block support
    if ( ! function_exists( 'register_block_type' ) ) {
        return;
    }

    // Register the block
    register_block_type( 'kwik-faqs/filter', array(
        'editor_script' => 'kwik-faq-filter-block-editor',
        'style' => 'kwik-faq-filter-widget',
        'script' => 'kwik-faq-filter-widget',
        'render_callback' => 'kwik_faqs_render_filter_block',
        'attributes' => array(
            'title' => array(
                'type' => 'string',
                'default' => __( 'Search FAQs', 'kwik' ),
            ),
            'placeholder' => array(
                'type' => 'string',
                'default' => __( 'Search FAQs...', 'kwik' ),
            ),
            'showResultsCount' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'className' => array(
                'type' => 'string',
                'default' => '',
            ),
        ),
    ) );
}
add_action( 'init', 'kwik_faqs_register_filter_block' );

/**
 * Render the FAQ filter block
 * 
 * @param array $attributes Block attributes
 * @return string Block HTML
 */
function kwik_faqs_render_filter_block( $attributes ) {
    // Only show on FAQ archive pages unless in admin/editor
    if ( ! is_admin() && ! is_post_type_archive( 'faqs' ) ) {
        return '';
    }

    // Default attributes
    $title = isset( $attributes['title'] ) ? $attributes['title'] : __( 'Search FAQs', 'kwik' );
    $placeholder = isset( $attributes['placeholder'] ) ? $attributes['placeholder'] : __( 'Search FAQs...', 'kwik' );
    $show_results_count = isset( $attributes['showResultsCount'] ) ? $attributes['showResultsCount'] : true;
    $class_name = isset( $attributes['className'] ) ? $attributes['className'] : '';

    // Build CSS classes
    $classes = array( 'kwik-faq-filter-block' );
    if ( ! empty( $class_name ) ) {
        $classes[] = $class_name;
    }

    // Start output buffering
    ob_start();
    ?>
    
    <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
        <?php if ( ! empty( $title ) ): ?>
            <h3 class="faq-filter-title"><?php echo esc_html( $title ); ?></h3>
        <?php endif; ?>
        
        <div class="faq-filter-widget-container glass-panel">
            <label for="faq-search-block" class="screen-reader-text"><?php esc_html_e( 'Search FAQs', 'kwik' ); ?></label>
            <input 
                type="search" 
                id="faq-search-block" 
                class="faq-search-input faq-search-widget" 
                placeholder="<?php echo esc_attr( $placeholder ); ?>"
                aria-label="<?php esc_attr_e( 'Search FAQs', 'kwik' ); ?>"
            >
            <?php if ( $show_results_count ): ?>
                <div class="faq-search-results-count faq-widget-results-count" id="faq-block-results-count" aria-live="polite"></div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Enqueue block editor assets
 */
function kwik_faqs_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'kwik-faq-filter-block-editor',
        KWIK_FAQS_URL . 'js/faq-filter-block.js',
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
        KWIK_FAQS_VERSION,
        true
    );
}
add_action( 'enqueue_block_editor_assets', 'kwik_faqs_enqueue_block_editor_assets' );

/**
 * Enqueue front-end block assets
 */
function kwik_faqs_enqueue_block_assets() {
    // Only enqueue on pages that might have the block
    if ( has_block( 'kwik-faqs/filter' ) || is_post_type_archive( 'faqs' ) ) {
        wp_enqueue_script(
            'kwik-faq-filter-widget',
            KWIK_FAQS_URL . 'js/faq-filter-widget.js',
            array( 'jquery' ),
            KWIK_FAQS_VERSION,
            true
        );

        wp_enqueue_style(
            'kwik-faq-filter-widget',
            KWIK_FAQS_URL . 'css/faq-filter-widget.css',
            array(),
            KWIK_FAQS_VERSION
        );
    }
}
add_action( 'wp_enqueue_scripts', 'kwik_faqs_enqueue_block_assets' );

/**
 * Add block category for FAQ blocks
 */
function kwik_faqs_add_block_category( $categories ) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'kwik-faqs',
                'title' => __( 'FAQ Tools', 'kwik' ),
                'icon' => 'lightbulb',
            ),
        )
    );
}
add_filter( 'block_categories_all', 'kwik_faqs_add_block_category' );
