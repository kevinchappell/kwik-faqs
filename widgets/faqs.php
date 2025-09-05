<?php
/**
 * Widget Name: Kwik FAQs
 * Description: list the most frequently asked questions
 * Version: 1.0.0
 * Author: kevinchappell
 *
 * @package KwikFAQs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register our widget on widgets_init
 *
 * @since 1.0.0
 */
add_action( 'widgets_init', 'kwik_faqs_register_widget' );

/**
 * Register widget function
 */
function kwik_faqs_register_widget(): void {
    register_widget( 'Kwik_FAQs_Widget' );
}

/**
 * FAQs Widget Class
 *
 * @since 1.0.0
 */
class Kwik_FAQs_Widget extends WP_Widget
{
    /**
     * Widget setup.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $widget_ops = array(
            'classname'   => 'kwik_faqs_widget',
            'description' => esc_html__( 'Display your FAQs with customizable options', 'kwik' ),
        );

        $control_ops = array(
            'width'  => 400,
            'height' => 350,
            'id_base' => 'kwik-faqs-widget',
        );

        parent::__construct( 'kwik-faqs-widget', esc_html__( 'Kwik FAQs', 'kwik' ), $widget_ops, $control_ops );
    }

    public function add_style($cpr)
    {
        $width = $cpr !== 0 ? 100 / $cpr : 100;
        $width = $width - 2 + (2 / $cpr); // factor in the margin-right
        $add_style = '<style type="text/css">';
        $add_style .= '.cpt_faqs_widget .faq{width:' . round($width, 2) . '%}';
        $add_style .= '.cpt_faqs_widget .faq.nth-faq-' . $cpr . '{margin-right:0}';
        $add_style .= '</style>';
        echo $add_style;
    }

    /**
     * Render the widget for users
     */
    public function widget($args, $instance)
    {
        extract($args);

        // variables from widget settings.
        $title = apply_filters('widget_title', $instance['title']);
        $orderby = $instance['orderby'];
        $order = $instance['order'];
        $faqs_per_row = intval($instance['faqs_per_row']);
        $show_thumbs = isset($instance['show_thumbs']) ? 1 : 0;

        $args = array(
            'levels' => $instance['levels'],
            'orderby' => $instance['orderby'],
            'order' => $instance['order'],
            'show_thumbs' => $instance['show_thumbs'],
        );

        // custom styling based on widget settings
        self::add_style($faqs_per_row);

        echo $before_widget;

        /* Display the widget title if one was input (before and after defined by themes). */
        if ($title) {
            echo $before_title . $title . $views_posts_link . $after_title;
        }

        foreach ($instance['levels'] as $level) {
            $args['level'] = $level;
            KwikFAQs::faq_logos($args);
        }

        echo $after_widget;
    }

    /**
     * Update widget settings.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        /* Strip tags for title and name to remove HTML (important for text inputs). */
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['levels'] = $new_instance['levels'];
        $instance['orderby'] = strip_tags($new_instance['orderby']);
        $instance['order'] = strip_tags($new_instance['order']);
        $instance['show_thumbs'] = $new_instance['show_thumbs'];
        $instance['faqs_per_row'] = strip_tags($new_instance['faqs_per_row']);
        return $instance;
    }

    /**
     * Widget settings form
     */
    public function form($instance)
    {
        $inputs = new KwikInputs();

        // Set up some default widget settings.
        $defaults = array('title' => esc_html__('Member Companies', 'kwik'),
            'levels' => array(),
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'show_thumbs' => 0,
            'faqs_per_row' => 6,
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        // Widget Title: Text Input
        $output = $inputs->text($this->get_field_name('title'), $instance['title'], __('Title: ', 'kwik'));

        // FAQ Levels
        $terms = get_terms("faq_topics", 'orderby=id&hide_empty=0');
        $output .= $inputs->markup('h3', __('Levels: ', 'kwik'));

        foreach ($terms as $term) {
            $cbAttrs = array(
                'id' => $this->get_field_name('levels') . '-' . $term->slug,
                'checked' => $instance['levels'][$term->slug] ? true : false,
            );
            $output .= $inputs->cb($this->get_field_name('levels') . '[' . $term->slug . ']', $term->slug, $term->name . ': ', $cbAttrs);
        }

        $output .= $inputs->select($this->get_field_name('orderby'), $instance['orderby'], __('Order By: ', 'kwik'), null, KwikHelpers::order_by());
        $output .= $inputs->select($this->get_field_name('order'), $instance['order'], __('Order: ', 'kwik'), null, KwikHelpers::order());
        $output .= $inputs->spinner($this->get_field_name('faqs_per_row'), $instance['faqs_per_row'], __('FAQs per Row: ', 'kwik'), array('min' => '1', 'max' => '6'));
        $output .= $inputs->cb($this->get_field_name('show_thumbs'), true, __('Show thumbnails: ', 'kwik'), array('checked' => $instance['show_thumbs'] ? true : false));

        echo $output;

    }
}
