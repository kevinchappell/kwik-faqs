<?php

class K_FAQS_HELPERS extends KwikFAQs
{
    public function __construct()
    {
        $this->name = "K_FAQS_HELPERS";
    }

    public function array_insert_at_position($array, $values, $pivot, $position = 'after')
    {
        $offset = 0;
        foreach ($array as $key => $value) {
            ++$offset;
            if ($key == $pivot) {
                break;
            }
        }

        if ($position == 'before') {
            --$offset;
        }

        return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, null, true);
    }

    public static function faqs_at_a_glance()
    {
        KwikUtils::cpt_at_a_glance('faqs');
    }

    public static function k_faq_logo_text_filter($translated_text, $untranslated_text, $domain)
    {
        global $post, $typenow, $current_screen;

        if (is_admin() && 'faqs' === $typenow) {
            switch ($untranslated_text) {

            case 'Insert into post':
                $translated_text = __('Add FAQ answer', 'kwik');
                break;

            // case 'Set featured image':
            //     $translated_text = __('Set FAQ logo', 'kwik');
            //     break;

            // case 'Set Featured Image':
            //     $translated_text = __('Set FAQ Logo', 'kwik');
            //     break;

            // case 'Featured Image':
            //     $translated_text = __('FAQ Logo', 'kwik');
            //     break;

            case 'Enter title here':
                $translated_text = __('Enter Question', 'kwik');
                break;

            case 'Title':
                $translated_text = __('Question', 'kwik');
                break;
            }
        }
        return $translated_text;
    }
}
