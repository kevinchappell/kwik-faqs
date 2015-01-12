<?php

require_once 'class.helpers.php';

class KwikFAQs {
  static $helpers;

  public function __construct() {

    add_action('init', array( $this, 'faqs_create_post_type' ) );
    add_filter('archive_template', array( $this, 'archive_template' ));
    add_filter('single_template', array( $this, 'single_template' ));

    if ( is_admin() ){
      $this->admin();
    } else {
      add_action('wp_enqueue_scripts', array( $this, 'scripts_and_styles' ));
    }

    // widgets
    self::load_widgets();

    // Cleanup on deactivation
    register_deactivation_hook( __FILE__, array( $this, '__destruct' ) );
  }

  public function __destruct() {
    // Do garbage cleanup stuff here
  }

  public function admin() {
    if ( !$this->admin ) {
      require_once __DIR__ . '/class.kwik-faqs-admin.php';
      $this->admin = new KwikFAQs_Admin( $this );
    }
    return $this->admin;
  }

  public function scripts_and_styles() {
    wp_enqueue_script('jquery-cycle', 'http://malsup.github.io/min/jquery.cycle2.min.js', array('jquery'));
    wp_enqueue_style('kwik-faqs-css', K_FAQS_URL . '/css/' . K_FAQS_BASENAME . '.css', false, '2014-12-31');
  }

  public function faqs_create_post_type() {

    self::create_faqs_taxonomies();
    // new K_FAQS_META();

    register_post_type( K_FAQS_CPT,
      array(
        'labels' => array(
          'name' => __( 'FAQs', 'kwik' ),
          'all_items' => __( 'FAQs', 'kwik' ),
          'singular_name' => __( 'FAQ', 'kwik' ),
          'add_new' => __( 'Add FAQ', 'kwik' ),
          'add_new_item' => __( 'Add New FAQ', 'kwik' ),
          'edit_item' => __( 'Edit FAQ', 'kwik' ),
          'menu_name' => __( 'FAQs', 'kwik' )
        ),
        'menu_icon' => 'dashicons-lightbulb',
        'menu_position' => 4,

      'supports' => array('title','editor', 'thumbnail'),
      'public' => true,
      'exclude_from_search' => false,
      'has_archive' => true,
      'taxonomies' => array('faq_topics'),
      // 'register_meta_box_cb' => 'add_faqs_metabox',
      'rewrite' => array('slug' => K_FAQS_CPT),
      'query_var' => true
      )
    );

    add_image_size( 'faq_logo', 240, 240, false );
    flush_rewrite_rules(false);
  }


  public function create_faqs_taxonomies() {

    $faq_levels_labels = array(
      'name' => _x( 'Topic', 'taxonomy general name' ),
      'singular_name' => _x( 'Topic', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search Topics' ),
      'all_items' => __( 'All Topics' ),
      'edit_item' => __( 'Edit Topic' ),
      'update_item' => __( 'Update Topic' ),
      'add_new_item' => __( 'Add New Topic' ),
      'new_item_name' => __( 'New Topic' )
    );

    register_taxonomy( 'faq_levels', array( K_FAQS_CPT ), array(
      'hierarchical' => false,
      'labels' => $faq_levels_labels,
      'show_ui' => true,
      'query_var' => true,
      'show_admin_column' => true,
      'rewrite' => array('slug' => 'faq-topic')
    ));

  }





public function archive_template($archive) {
  global $wp_query, $post;

  /* Checks for single template by post type */
  if ($post->post_type === K_FAQS_CPT){
  if(file_exists(K_FAQS_PATH. '/template/'.K_FAQS_CPT.'-archive.php'))
    return K_FAQS_PATH. '/template/'.K_FAQS_CPT.'-archive.php';
  }
  return $archive;
}

public function single_template($single) {
  global $wp_query, $post;

  /* Checks for single template by post type */
  if ($post->post_type === K_FAQS_CPT){
  if(file_exists(K_FAQS_PATH. '/template/'.K_FAQS_CPT.'-single.php'))
    return K_FAQS_PATH. '/template/'.K_FAQS_CPT.'-single.php';
  }
  return $single;
}



/**
 * Adds `membership_table` shortcode.
 * @param  [Array] $atts array of attribute to pass
 * @return [String]      Markup to display array of faq data
 *
 * Usage: [membership_table foo="foo-value"]
 * TODO: use Kwik Framework markup generator
 */
public function membership_table( $atts ) {
  extract( shortcode_atts( array(
    'foo' => 'something',
    'bar' => 'something else'
  ), $atts ) );

  $memb_table = '<!-- BEGIN [membership_table] -->';
  $terms = get_terms("faq_levels", 'orderby=id&hide_empty=0&exclude=27' );

  $memb_table .= '<table class="mem_table" cellpadding="5">
    <thead>
      <tr>';
        $memb_table .= '<th class="column-mem_level_img"></th>';
        $memb_table .= '<th class="column-mem_level">'.__('Membership Level','kwik').'</th>';
        $memb_table .= '<th class="column-fee">'.__('Annual Fee*', 'kwik' ).'</th>';
        $memb_table .= '<th class="column-fte">'.__('FTEs', 'kwik' ).'</th>';
        // $memb_table .= '<th class="column-ipc">'.__('IP Contribution', 'kwik' ).'</th>';
        $memb_table .= '<th class="column-tsc">'.__('Technical Steering Commitee', 'kwik' ).'</th>';
        $memb_table .= '<th class="column-position">'.__('Board/Voting <br/>Position','kwik').'</th>';
      $memb_table .= '</tr>
    </thead>
    <tbody data-post-type="faq_levels">';

    foreach ($terms as $term) {
      $t_id = $term->term_id;
      $term_meta = get_option( "taxonomy_$t_id" );
      $img = '';

      if(function_exists('taxonomy_image_plugin_get_image_src'))  {
        $associations = taxonomy_image_plugin_get_associations();
        if ( isset( $associations[ $term->term_id ] ) ) {
          $attachment_id = (int) $associations[ $term->term_id ];
          $img = wp_get_attachment_image( $attachment_id, 'medium');
        }
      }

      $memb_table .= '<tr>';
        $memb_table .= '<td class="mem_level_img">'.$img.'</td>';
        $memb_table .= '<td class="mem_level_name">'.$term->name.'</td>';
        $memb_table .= '<td>'.(esc_attr( $term_meta['fee'][0] ) ? esc_attr( $term_meta['fee'][0] ) : '0');
        $memb_table .= (esc_attr( $term_meta['fee'][1] ) ? '<br><em>'.esc_attr( $term_meta['fee'][1] ).'</em>' : '');

        $memb_table .= '</td>';
        $memb_table .= '<td>'.(esc_attr( $term_meta['fte'] ) ? esc_attr( $term_meta['fte'] ) : '0').'</td>';
        // $memb_table .= '<td>'.(esc_attr( $term_meta['ipc'] ) ? esc_attr( $term_meta['ipc'] ) : '').'</td>';
        $memb_table .= '<td>'.(esc_attr( $term_meta['tsc'] ) ? esc_attr( $term_meta['tsc'] ) : '').'</td>';
        $memb_table .= '<td>'.(esc_attr( $term_meta['position'] ) ? esc_attr( $term_meta['position'] ) : '').'</td>';
      $memb_table .= '</tr>';
    }
    $memb_table .= '</tbody></table><em style="font-size: 12px;">*'.__('Fee in US Dollars.', 'kwik').'</em>';
    $memb_table .= '<!-- END [membership_table] -->';

  return $memb_table;
}



  public function faq_logos($args){
    $inputs = new KwikInputs();

    $term = get_term_by( 'slug', $args['level'], 'faq_levels');

    $cl = $inputs->markup('h3', $term->name.' Members');
    $query_args = array(
      'post_status' => 'publish',
      'post_type' => K_FAQS_CPT,
      'faq_levels' => $args['level'],
      'orderby' => $args['orderby'],
      'order' => $args['order']
    );

    $faq_query = new WP_Query($query_args);

    $i = 1;
    $total = $faq_query->post_count;
    if ($faq_query->have_posts()):
      while ($faq_query->have_posts()) : $faq_query->the_post();
        global $more;
        $more = 0;

        $faq_id = get_the_ID();
        $faq_name = get_the_title($faq_id);
        $logo_or_name = (has_post_thumbnail() && $args['show_thumbs']) ? get_the_post_thumbnail($faq_id, 'faq_logo') : $faq_name;
        $faq = $inputs->markup('a', $logo_or_name, array('href' => get_the_permalink($faq_id), 'title' => $faq_name));
        $cl .= $inputs->markup('div', $faq, array("class"=>"faq faq-".$faq_id." nth-faq-".$i));
        $i++;
      endwhile;
    endif; wp_reset_postdata();

    $cl = $inputs->markup('div', $cl, array('class'=> array('member-level', $term->slug.'-members', 'clear')));

    echo $cl;
  }


  public function load_widgets(){
    foreach (glob(K_FAQS_PATH . "/widgets/*.php") as $inc_filename) {
      include $inc_filename;
    }
  }

} // / Class KwikFAQs


// Singleton
function kwik_faqs(){
  global $kwik_faqs;
  if ( !$kwik_faqs ) {
    $kwik_faqs = new KwikFAQs();
  }
  return $kwik_faqs;
}
