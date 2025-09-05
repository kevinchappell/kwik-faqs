<?php
/**
 * Admin functionality for Kwik FAQs.
 *
 * @package KwikFAQs
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KwikFAQs_Admin
{
    /**
     * Main plugin instance
     *
     * @var KwikFAQs
     */
    private KwikFAQs $main_instance;

    /**
     * Constructor
     *
     * @param KwikFAQs $main_instance Main plugin instance.
     */
    public function __construct( KwikFAQs $main_instance )
    {
        $this->main_instance = $main_instance;

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_filter( 'manage_' . KWIK_FAQS_CPT . '_posts_columns', array( $this, 'set_faqs_columns' ) );
        add_action( 'wp_ajax_faqs_update_post_order', array( $this, 'update_post_order' ) );
        add_action( 'save_post_' . KWIK_FAQS_CPT, array( $this, 'save_faqs_meta' ), 10, 3 );
        add_action( 'admin_menu', array( $this, 'register_faqs_menu' ) );

        // Utils/Helpers
        add_filter( 'gettext', array( 'K_FAQS_HELPERS', 'k_faq_logo_text_filter' ), 20, 3 );
        add_action( 'dashboard_glance_items', array( 'K_FAQS_HELPERS', 'faqs_at_a_glance' ) );

        // Cleanup on deactivation
        add_action( 'switch_theme', array( $this, 'deactivate' ) );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current page hook.
     */
    public function enqueue_admin_scripts( string $hook ): void
    {
        $screen = get_current_screen();

        if ( ! $screen ) {
            return;
        }

        $post_types_array = array(
            KWIK_FAQS_CPT,
            KWIK_FAQS_CPT . '_page_faqs-order',
        );

        // Check screen hook and current post type
        if ( in_array( $screen->post_type, $post_types_array, true ) ||
             ( 'toplevel_page_faqs-order' === $hook ) ) {
            wp_enqueue_script(
                'jquery-ui-autocomplete',
                false,
                array( 'jquery' ),
                null,
                true
            );
            wp_enqueue_script(
                'jquery-ui-sortable',
                false,
                array( 'jquery' ),
                null,
                true
            );
            wp_enqueue_script(
                'kwik-faqs-admin',
                KWIK_FAQS_URL . 'js/kwik-faqs-admin.js',
                array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ),
                KWIK_FAQS_VERSION,
                true
            );
            wp_enqueue_script(
                'kwik-faqs',
                KWIK_FAQS_URL . 'js/kwik-faqs.js',
                array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ),
                KWIK_FAQS_VERSION,
                true
            );
        }
    }

    private function faqs_in_right_now()
    {

        $post_type = 'faqs';

        if (!post_type_exists($post_type)) {
            return;
        }
        $num_posts = wp_count_posts($post_type);
        echo '';
        $num = number_format_i18n($num_posts->publish);
        $text = _n('User Submission', 'User Submissions', $num_posts->publish);
        if (current_user_can('edit_posts')) {
            $num = '<a href="edit.php?post_type=' . $post_type . '">' . $num . '</a>';
            $text = '<a href="edit.php?post_type=' . $post_type . '">' . $text . '</a>';
        }
        echo '<td class="first b b-faqs">' . $num . '</td>';
        echo '<td class="t faqs">' . $text . '</td>';
        if ($num_posts->pending > 0) {
            $num = number_format_i18n($num_posts->pending);
            $text = _n('User Submission Pending', 'User Submissions Pending', intval($num_posts->pending));
            if (current_user_can('edit_posts')) {
                $num = '<a href="edit.php?post_status=pending&post_type=' . $post_type . '">' . $num . '</a>';
                $text = '<a href="edit.php?post_status=pending&post_type=' . $post_type . '">' . $text . '</a>';
            }
            echo '<td class="first b b-faqs">' . $num . '</td>';
            echo '<td class="t faqs">' . $text . '</td>';
        }

        echo '</tr>';
    }

    public function set_faqs_columns($columns)
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Question'),
            'answer' => __('Answer'),
            'topic' => __('Topic'),
            'date' => __('Date'),
        );
    }

    // Add the meta box
    public function add_faqs_metabox()
    {
        add_meta_box('faqs_meta', 'FAQ Meta Data', 'faqs_meta', 'faqs', 'normal', 'default');
    }

    public function faqs_meta()
    {
        global $post;

        $post_link = get_post_meta($post->ID, '_post_link', true);
        $user_info = get_post_meta($post->ID, '_user_info', false);
        $user_info = (is_array($user_info) && !empty($user_info) ? $user_info[0] : '');

        $faqs_meta = '';
        // Noncename for security check on data origin
        $faqs_meta .= '<input type="hidden" name="faqs_meta_noncename" id="faqs_meta_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
        $faqs_meta .= '<div class="meta_wrap">';
        $faqs_meta .= '<ul>';
        //$faqs_meta .= '<li><strong>'.__('Belt Link','kwik').':</strong></li>';
        $faqs_meta .= '<li><label>' . __('Post Link', 'kwik') . '</label><input type="text" name="_post_link_title" id="post_link_title"" value="' . ($post_link != "" ? get_the_title($post_link) : "") . '" /><input type="hidden" id="post_link_id" name="_post_link" value="' . $post_link . '" /><label>&nbsp;</label><small>Type the name of the linked content and select from list</small></li>';
        $faqs_meta .= '</ul>';
        $faqs_meta .= '</div>';

        $faqs_meta .= '<div class="meta_wrap user_info">';
        $faqs_meta .= '<h4>' . __('Customer Info', 'kwik') . ':</h4>';
        $faqs_meta .= (isset($user_info[1]) ? get_avatar($user_info[1], 200) : '');
        $faqs_meta .= '<ul>';

        $faqs_meta .= '<li><label>' . __('Name', 'kwik') . '</label><input type="text" name="_user_info[]" value="' . (isset($user_info[0]) ? $user_info[0] : '') . '" /></li>';
        $faqs_meta .= '<li><label>' . (isset($user_info[1]) ? '<a href="mailto:' . $user_info[1] . '?subject=Your%20submission%20has%20been%20approved!&body=Check%20out%20your%20Action%20Shot%20on%20TopRopeBelts.com%20here: ' . get_permalink($post->ID) . '" title="' . (isset($user_info[0]) ? 'Send email to ' . $user_info[0] : '') . '">' . __('Email', 'kwik') . '</a>':__('Email', 'kwik')) . '</label><input type="text" name="_user_info[]" value="' . (isset($user_info[1]) ? $user_info[1] : '') . '" /></li>';
        $faqs_meta .= '<li><label>' . __('Phone', 'kwik') . '</label><input type="text" name="_user_info[]" value="' . (isset($user_info[2]) ? $user_info[2] : '') . '" /></li>';
        $faqs_meta .= '<li><label>' . __('URL', 'kwik') . '</label><input type="text" name="_user_info[]" value="' . (isset($user_info[3]) ? $user_info[3] : '') . '" /></li>';
        $faqs_meta .= '<li><label>' . __('Twitter', 'kwik') . '</label><input type="text" name="_user_info[]" value="' . (isset($user_info[4]) ? $user_info[4] : '') . '" /></li>';
        $faqs_meta .= '<li><label>' . __('Publish User Info?', 'kwik') . '</label><input type="checkbox" name="_user_info[]" ' . checked(1, $user_info[5], false) . ' value="1" /></li>';
        $faqs_meta .= '</ul>';
        $faqs_meta .= '</div>';

        $faqs_meta .= '<br class="clear"/>';

        echo $faqs_meta;

    }

    /**
     * Save FAQ meta data
     *
     * @param int      $post_id Post ID.
     * @param WP_Post  $post    Post object.
     * @param bool     $update  Whether this is an update.
     */
    public function save_faqs_meta( int $post_id, WP_Post $post, bool $update ): void
    {
        // Verify user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Skip for revisions and auto-drafts
        if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
            return;
        }

        // Verify nonce
        if ( ! isset( $_POST['faqs_meta_noncename'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['faqs_meta_noncename'] ) ), plugin_basename( __FILE__ ) ) ) {
            return;
        }

        // Sanitize and save meta data
        if ( isset( $_POST['_post_link'] ) ) {
            update_post_meta( $post_id, '_post_link', sanitize_text_field( wp_unslash( $_POST['_post_link'] ) ) );
        }

        if ( isset( $_POST['_user_info'] ) && is_array( $_POST['_user_info'] ) ) {
            $user_info = array_map( 'sanitize_text_field', wp_unslash( $_POST['_user_info'] ) );

            // Handle URL validation
            if ( ! empty( $user_info[3] ) && ! preg_match( '#https?://#', $user_info[3] ) ) {
                $user_info[3] = 'http://' . $user_info[3];
            }

            // Handle Twitter handle
            if ( ! empty( $user_info[4] ) && preg_match( '/@([a-z0-9_]+)/i', $user_info[4] ) ) {
                $user_info[4] = str_replace( '@', '', $user_info[4] );
            }

            update_post_meta( $post_id, '_user_info', $user_info );
        }
    }

    public function register_faqs_menu()
    {
        add_submenu_page('edit.php?post_type=faqs', 'Order FAQs', 'Order', 'edit_pages', 'faqs-order', array($this, 'faqs_order_page'));
    }

    public function faqs_order_page()
    {
        ?>
<div class="wrap">

      <h2>Sort FAQs</h2>

      <p>Simply drag the faq up or down and they will be saved in the order the appear here.</p>

<?php

        $terms = get_terms("faq_topics", 'orderby=id&hide_empty=1');

        foreach ($terms as $term) {
            $faqs = new WP_Query(array(
                'post_type' => 'faqs',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => $term->taxonomy,
                        'field' => 'id',
                        'terms' => $term->term_id, // Where term_id of Term 1 is "1".
                        'include_children' => false,
                    ),
                ),
                'order' => 'ASC',
                'orderby' => 'menu_order',
            ));
            echo '<h1>' . $term->name . ' Level</h1>';
            if ($faqs->have_posts()): ?>
<table class="wp-list-table widefat fixed posts" id="sortable-table">
            <thead>
              <tr>
                <th class="column-order">Order</th>
                <th class="column-thumbnail">Thumbnail</th>
                <th class="column-title">Title</th>
              </tr>
            </thead>
            <tbody data-post-type="faqs">
<?php
while ($faqs->have_posts()):$faqs->the_post();
                ?>

	              <tr id="post-<?php the_ID();?>">
	                <td class="column-order"><img src="<?php echo get_stylesheet_directory_uri() . '/images/icons/move.png';?>" title="" alt="Move Slide" width="30" height="30" class="" /></td>
	                <td class="column-thumbnail"><?php the_post_thumbnail('faq_logo');?></td>
	                <td class="column-title">
	                              <strong><?php the_title();?></strong>
	                              <div class="excerpt"><?php the_excerpt();?></div>
	                          </td>
	              </tr>
	<?php endwhile;?>
</tbody>
            <tfoot>
              <tr>
                <th class="column-order">Order</th>
                <th class="column-thumbnail">Thumbnail</th>
                <th class="column-title">Title</th>
              </tr>
            </tfoot>
          </table>

<?php else: ?>
<p>No faqs found, why not <a href="post-new.php?post_type=faqs">add one?</a></p>
<?php endif;?>

<?php wp_reset_postdata(); // Don't forget to reset again!
        }?>
</div><!-- .wrap -->

<?php
}

    public function faqs_update_post_order()
    {
        global $wpdb;
        $post_type = $_POST['postType'];
        $order = $_POST['order'];
        /**
         *    Expect: $sorted = array(
         *                menu_order => post-XX
         *            );
         */
        foreach ($order as $menu_order => $post_id) {
            $post_id = intval(str_ireplace('post-', '', $post_id));
            $menu_order = intval($menu_order);
            wp_update_post(array(
                'ID' => $post_id,
                'menu_order' => $menu_order,
            ));
        }
        die('1');
    }

}
