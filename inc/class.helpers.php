<?php
/**
 * Helper functions and utilities for Kwik FAQs.
 *
 * @package KwikFAQs
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class K_FAQS_HELPERS
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize if needed
    }

    /**
     * Insert array values at a specific position.
     *
     * @param array  $array    Array to modify.
     * @param array  $values   Values to insert.
     * @param mixed  $pivot    Pivot key to insert after/before.
     * @param string $position Position to insert (before/after).
     * @return array Modified array.
     */
    public static function array_insert_at_position( array $array, array $values, $pivot, string $position = 'after' ): array
    {
        $offset = 0;
        foreach ( $array as $key => $value ) {
            ++$offset;
            if ( $key === $pivot ) {
                break;
            }
        }

        if ( 'before' === $position ) {
            --$offset;
        }

        return array_slice( $array, 0, $offset, true ) + $values + array_slice( $array, $offset, null, true );
    }

    /**
     * Display FAQs count on the dashboard "At a Glance" widget.
     */
    public static function faqs_at_a_glance(): void
    {
        if ( ! post_type_exists( KWIK_FAQS_CPT ) ) {
            return;
        }

        $num_posts = wp_count_posts( KWIK_FAQS_CPT );
        $num = number_format_i18n( $num_posts->publish ?? 0 );
        $text = _n( 'FAQ', 'FAQs', $num_posts->publish ?? 0, 'kwik' );

        if ( current_user_can( 'edit_posts' ) ) {
            $num = '<a href="edit.php?post_type=' . KWIK_FAQS_CPT . '">' . $num . '</a>';
            $text = '<a href="edit.php?post_type=' . KWIK_FAQS_CPT . '">' . $text . '</a>';
        }

        echo '<li class="faqs-count">' . $num . ' ' . $text . '</li>';
    }

    /**
     * Filter text strings in admin to customize FAQ interface.
     *
     * @param string $translated_text   Translated text.
     * @param string $untranslated_text Original text.
     * @param string $domain            Text domain.
     * @return string Filtered text.
     */
    public static function k_faq_logo_text_filter( string $translated_text, string $untranslated_text, string $domain ): string
    {
        global $typenow;

        if ( is_admin() && KWIK_FAQS_CPT === $typenow ) {
            switch ( $untranslated_text ) {
                case 'Insert into post':
                    $translated_text = __( 'Add FAQ answer', 'kwik' );
                    break;

                case 'Enter title here':
                    $translated_text = __( 'Enter Question', 'kwik' );
                    break;

                case 'Title':
                    $translated_text = __( 'Question', 'kwik' );
                    break;
            }
        }
        return $translated_text;
    }
}
