<?php
/**
 * Widget Name: Kwik FAQ Filter
 * Description: Provides a search/filter interface for FAQ archives
 * Version: 1.0.0
 * Author: kevinchappell
 *
 * @package KwikFAQs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register our FAQ filter widget on widgets_init
 *
 * @since 1.0.0
 */
add_action( 'widgets_init', 'kwik_faq_filter_register_widget' );

/**
 * Register FAQ filter widget function
 */
function kwik_faq_filter_register_widget(): void {
    register_widget( 'Kwik_FAQ_Filter_Widget' );
}

/**
 * FAQ Filter Widget Class
 *
 * @since 1.0.0
 */
class Kwik_FAQ_Filter_Widget extends WP_Widget
{
    /**
     * Widget setup.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $widget_ops = array(
            'classname'   => 'kwik_faq_filter_widget',
            'description' => esc_html__( 'Provides search/filter functionality for FAQ archives', 'kwik' ),
        );

        $control_ops = array(
            'width'  => 400,
            'height' => 250,
            'id_base' => 'kwik-faq-filter-widget',
        );

        parent::__construct( 'kwik-faq-filter-widget', esc_html__( 'FAQ Filter', 'kwik' ), $widget_ops, $control_ops );
    }

    /**
     * How to display the widget on the screen.
     *
     * @since 1.0.0
     * @param array $args     Widget arguments.
     * @param array $instance Widget instance.
     */
    public function widget( $args, $instance ): void
    {
        // Only show on FAQ archive pages
        if ( ! is_post_type_archive( 'faqs' ) ) {
            return;
        }

        extract( $args );

        // Widget options
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
        $placeholder = ! empty( $instance['placeholder'] ) ? $instance['placeholder'] : __( 'Search FAQs...', 'kwik' );
        $show_results_count = ! empty( $instance['show_results_count'] ) ? $instance['show_results_count'] : false;

        echo $before_widget;

        if ( $title ) {
            echo $before_title . $title . $after_title;
        }

        // FAQ Filter HTML
        ?>
        <div class="faq-filter-widget-container glass-panel">
            <label for="faq-search-widget" class="screen-reader-text"><?php esc_html_e( 'Search FAQs', 'kwik' ); ?></label>
            <input 
                type="search" 
                id="faq-search-widget" 
                class="faq-search-input faq-search-widget" 
                placeholder="<?php echo esc_attr( $placeholder ); ?>"
                aria-label="<?php esc_attr_e( 'Search FAQs', 'kwik' ); ?>"
            >
            <?php if ( $show_results_count ): ?>
                <div class="faq-search-results-count faq-widget-results-count" id="faq-widget-results-count" aria-live="polite"></div>
            <?php endif; ?>
        </div>
        <?php

        echo $after_widget;

        // Enqueue the filter script if not already enqueued
        $this->enqueue_filter_scripts();
    }

    /**
     * Update the widget settings.
     *
     * @since 1.0.0
     * @param array $new_instance New widget instance.
     * @param array $old_instance Old widget instance.
     * @return array Updated widget instance.
     */
    public function update( $new_instance, $old_instance ): array
    {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['placeholder'] = ( ! empty( $new_instance['placeholder'] ) ) ? sanitize_text_field( $new_instance['placeholder'] ) : '';
        $instance['show_results_count'] = ! empty( $new_instance['show_results_count'] ) ? 1 : 0;

        return $instance;
    }

    /**
     * Displays the widget settings controls on the widget panel.
     *
     * @since 1.0.0
     * @param array $instance Widget instance.
     */
    public function form( $instance ): void
    {
        // Default widget settings
        $defaults = array(
            'title' => esc_html__( 'Search FAQs', 'kwik' ),
            'placeholder' => esc_html__( 'Search FAQs...', 'kwik' ),
            'show_results_count' => false,
        );

        $instance = wp_parse_args( (array) $instance, $defaults );
        ?>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'kwik' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>"><?php esc_html_e( 'Placeholder Text:', 'kwik' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'placeholder' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['placeholder'] ); ?>" />
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_results_count'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_results_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_results_count' ) ); ?>" value="1" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_results_count' ) ); ?>"><?php esc_html_e( 'Show results count', 'kwik' ); ?></label>
        </p>

        <?php
    }

    /**
     * Enqueue the filter scripts for widget functionality
     *
     * @since 1.0.0
     */
    private function enqueue_filter_scripts(): void
    {
        // Only enqueue on FAQ archives
        if ( ! is_post_type_archive( 'faqs' ) ) {
            return;
        }

        // Check if script is already enqueued
        if ( wp_script_is( 'kwik-faq-filter-widget', 'enqueued' ) ) {
            return;
        }

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
