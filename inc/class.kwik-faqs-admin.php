<?php
/**
 * Admin functionality for Kwik FAQs.
 *
 * @package KwikFAQs
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
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
  public function __construct(KwikFAQs $main_instance)
  {
    $this->main_instance = $main_instance;

    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    add_filter('manage_' . KWIK_FAQS_CPT . '_posts_columns', array($this, 'set_faqs_columns'));
    add_action('wp_ajax_faqs_update_post_order', array($this, 'update_post_order'));
    add_action('admin_menu', array($this, 'register_faqs_menu'), 99);

    // Utils/Helpers
    add_filter('gettext', array('K_FAQS_HELPERS', 'k_faq_logo_text_filter'), 20, 3);
    add_action('dashboard_glance_items', array('K_FAQS_HELPERS', 'faqs_at_a_glance'));

    // Cleanup on deactivation
    add_action('switch_theme', array($this, 'deactivate'));
  }

  /**
   * Enqueue admin scripts and styles
   *
   * @param string $hook Current page hook.
   */
  public function enqueue_admin_scripts(string $hook): void
  {
    $screen = get_current_screen();

    if (!$screen) {
      return;
    }

    $post_types_array = array(
      KWIK_FAQS_CPT,
      KWIK_FAQS_CPT . '_page_faqs-order',
    );

    // Check screen hook and current post type
    if (
      in_array($screen->post_type, $post_types_array, true) ||
      ('toplevel_page_faqs-order' === $hook)
    ) {
      wp_enqueue_script(
        'jquery-ui-autocomplete',
        false,
        array('jquery'),
        null,
        true
      );
      wp_enqueue_script(
        'jquery-ui-sortable',
        false,
        array('jquery'),
        null,
        true
      );
      wp_enqueue_script(
        'kwik-faqs-admin',
        KWIK_FAQS_URL . 'js/kwik-faqs-admin.js',
        array('jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable'),
        KWIK_FAQS_VERSION,
        true
      );
      wp_enqueue_script(
        'kwik-faqs',
        KWIK_FAQS_URL . 'js/kwik-faqs.js',
        array('jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable'),
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
      'date' => __('Date'),
    );
  }

  public function register_faqs_menu()
  {
    // Ensure post type exists before registering menus
    if (!post_type_exists('faqs')) {
      // Log for debugging if WP_DEBUG_LOG is enabled
      if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('Kwik FAQs: Post type "faqs" does not exist when registering menu');
      }
      return;
    }

    // Register submenu pages under the FAQ post type
    add_submenu_page(
      'edit.php?post_type=faqs',
      'Order FAQs',
      'Order',
      'manage_options',
      'faqs-order',
      array($this, 'faqs_order_page')
    );

    add_submenu_page(
      'edit.php?post_type=faqs',
      'Import FAQs',
      'Import',
      'manage_options',
      'faqs-import',
      array($this, 'faqs_import_page')
    );
  }



  public function faqs_main_page()
  {
    echo '<div class="wrap"><h1>Kwik FAQs</h1><p>Main plugin page.</p></div>';
  }

  /**
   * Display the FAQ import page
   */
  public function faqs_import_page()
  {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
      wp_die('You do not have sufficient permissions to access this page.');
    }

    $import_result = null;
    
    // Handle form submission
    if (isset($_POST['kwik_faqs_import']) && isset($_FILES['faqs_json'])) {
      $import_result = $this->process_faqs_import();
    }

    ?>
    <div class="wrap">
      <h1>Import FAQs</h1>
      
      <?php if ($import_result): ?>
        <div class="notice notice-<?php echo esc_attr($import_result['type']); ?> is-dismissible">
          <p><?php echo wp_kses_post($import_result['message']); ?></p>
        </div>
      <?php endif; ?>
      
      <div class="card">
        <h2>Upload JSON File</h2>
        <p>Upload a JSON file containing your FAQs. The file should be an array of objects, with each object having a "question" and "answer" key.</p>
        
        <h3>Expected JSON Format:</h3>
        <pre style="background: #f1f1f1; padding: 10px; border-radius: 3px; font-size: 12px; max-width: 500px; overflow-x: auto;">[
  {
    "question": "Where is Top Rope Belts located?",
    "answer": "Our workshop and headquarters are located in Lexington, North Carolina..."
  },
  {
    "question": "Another FAQ question?",
    "answer": "The answer to the question..."
  }
]</pre>

        <form method="post" enctype="multipart/form-data" action="">
          <?php wp_nonce_field('kwik_faqs_import', 'kwik_faqs_import_nonce'); ?>
          <table class="form-table">
            <tr>
              <th scope="row"><label for="faqs_json">JSON File</label></th>
              <td>
                <input type="file" id="faqs_json" name="faqs_json" accept=".json,.txt" required />
                <p class="description">
                  Upload a JSON file with FAQ data. Maximum file size: <?php echo esc_html(wp_max_upload_size() / 1024 / 1024); ?>MB
                </p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="import_options">Import Options</label></th>
              <td>
                <fieldset>
                  <label>
                    <input type="checkbox" name="skip_duplicates" value="1" checked />
                    Skip duplicate questions (recommended)
                  </label><br>
                  <label>
                    <input type="checkbox" name="update_existing" value="1" />
                    Update existing FAQs if question matches (overwrites answers)
                  </label>
                </fieldset>
              </td>
            </tr>
          </table>
          <?php submit_button('Import FAQs', 'primary', 'kwik_faqs_import'); ?>
        </form>
      </div>
      
      <div class="card">
        <h3>Current FAQs</h3>
        <?php
        $existing_faqs = get_posts(array(
          'post_type' => 'faqs',
          'numberposts' => 5,
          'post_status' => 'publish'
        ));
        
        if ($existing_faqs): ?>
          <p>You currently have <strong><?php echo esc_html(wp_count_posts('faqs')->publish); ?></strong> published FAQs. Here are the most recent ones:</p>
          <ul>
            <?php foreach ($existing_faqs as $faq): ?>
              <li>
                <strong><?php echo esc_html($faq->post_title); ?></strong>
                <br><small><?php echo esc_html(wp_trim_words(strip_tags($faq->post_content), 15)); ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>No FAQs found. This import will be your first set of FAQs!</p>
        <?php endif; ?>
      </div>
    </div>
    
    <style>
      .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
      }
      .card h2, .card h3 {
        margin-top: 0;
      }
    </style>
    <?php
  }

  /**
   * Process the FAQ import from JSON file
   * 
   * @return array Result array with 'type' and 'message' keys
   */
  private function process_faqs_import(): array
  {
    // Check nonce for security
    if (
      !isset($_POST['kwik_faqs_import_nonce']) ||
      !wp_verify_nonce($_POST['kwik_faqs_import_nonce'], 'kwik_faqs_import')
    ) {
      return array('type' => 'error', 'message' => 'Security check failed.');
    }

    // Check if file was uploaded
    if (!isset($_FILES['faqs_json']) || $_FILES['faqs_json']['error'] !== UPLOAD_ERR_OK) {
      $upload_error = isset($_FILES['faqs_json']['error']) ? $_FILES['faqs_json']['error'] : 'Unknown error';
      $error_messages = array(
        UPLOAD_ERR_INI_SIZE => 'File is too large (exceeds upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE => 'File is too large (exceeds MAX_FILE_SIZE).',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
      );
      
      $error_msg = isset($error_messages[$upload_error]) ? $error_messages[$upload_error] : "File upload error (code: $upload_error).";
      return array('type' => 'error', 'message' => $error_msg);
    }

    // Check file size
    if ($_FILES['faqs_json']['size'] === 0) {
      return array('type' => 'error', 'message' => 'The uploaded file is empty.');
    }

    if ($_FILES['faqs_json']['size'] > wp_max_upload_size()) {
      return array('type' => 'error', 'message' => 'File size exceeds the maximum allowed size.');
    }

    // Read file content first - this is more reliable than extension checking
    $json_content = file_get_contents($_FILES['faqs_json']['tmp_name']);
    if ($json_content === false) {
      return array('type' => 'error', 'message' => 'Failed to read the uploaded file.');
    }

    // Trim whitespace and check for empty content
    $json_content = trim($json_content);
    if (empty($json_content)) {
      return array('type' => 'error', 'message' => 'The uploaded file contains no data.');
    }

    // Try to validate as JSON first - this is more important than the file extension
    $faqs_data = json_decode($json_content, true);
    $json_error = json_last_error();
    
    // If JSON is invalid, then check file extension for better error message
    if ($json_error !== JSON_ERROR_NONE) {
      // Check file type for better error reporting
      $filename = sanitize_file_name($_FILES['faqs_json']['name']);
      $manual_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
      $allowed_extensions = array('json', 'txt');
      
      // Debug information
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("FAQ Import Debug - Filename: $filename");
        error_log("FAQ Import Debug - Manual extension: $manual_ext");
        error_log("FAQ Import Debug - JSON Error: " . json_last_error_msg());
      }
      
      // If extension is wrong, show extension error
      if (!in_array($manual_ext, $allowed_extensions)) {
        return array(
          'type' => 'error', 
          'message' => "Invalid file type. Filename: '$filename', Detected extension: '$manual_ext'. Please upload a .json or .txt file containing JSON data."
        );
      }
      
      // Extension is OK but JSON is invalid - show JSON error
      $json_errors = array(
        JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error',
        JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters'
      );
      
      $error_message = isset($json_errors[$json_error]) ? $json_errors[$json_error] : json_last_error_msg();
      return array('type' => 'error', 'message' => "Invalid JSON file. Error: $error_message");
    }

    // Check if data is an array
    if (!is_array($faqs_data)) {
      return array('type' => 'error', 'message' => 'Invalid JSON format. Expected an array of FAQ objects, got: ' . gettype($faqs_data));
    }

    if (empty($faqs_data)) {
      return array('type' => 'warning', 'message' => 'The JSON file contains an empty array. No FAQs to import.');
    }

    // Get import options
    $skip_duplicates = isset($_POST['skip_duplicates']) && $_POST['skip_duplicates'] === '1';
    $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === '1';

    // Get existing FAQ titles for duplicate checking
    $existing_titles = array();
    if ($skip_duplicates || $update_existing) {
      $existing_posts = get_posts(array(
        'post_type' => 'faqs',
        'numberposts' => -1,
        'post_status' => 'any',
        'fields' => 'all'
      ));
      
      foreach ($existing_posts as $post) {
        $existing_titles[strtolower(trim($post->post_title))] = $post->ID;
      }
    }

    // Process each FAQ entry
    $imported_count = 0;
    $updated_count = 0;
    $skipped_count = 0;
    $errors = array();

    foreach ($faqs_data as $index => $faq) {
      // Validate FAQ structure
      if (!is_array($faq)) {
        $errors[] = "Entry #$index is not a valid object (found " . gettype($faq) . ").";
        continue;
      }

      // Validate required fields with better error messages
      if (!isset($faq['question']) || !isset($faq['answer'])) {
        $missing_fields = array();
        if (!isset($faq['question'])) $missing_fields[] = 'question';
        if (!isset($faq['answer'])) $missing_fields[] = 'answer';
        $errors[] = "Entry #$index is missing required field(s): " . implode(', ', $missing_fields);
        continue;
      }

      // Validate field types and content
      if (!is_string($faq['question']) || !is_string($faq['answer'])) {
        $errors[] = "Entry #$index has invalid data types. Both 'question' and 'answer' must be strings.";
        continue;
      }

      $question = trim($faq['question']);
      $answer = trim($faq['answer']);

      if (empty($question) || empty($answer)) {
        $errors[] = "Entry #$index has empty question or answer.";
        continue;
      }

      // Check for duplicates
      $question_lower = strtolower($question);
      if (isset($existing_titles[$question_lower])) {
        if ($update_existing) {
          // Update existing FAQ
          $post_id = wp_update_post(array(
            'ID' => $existing_titles[$question_lower],
            'post_content' => wp_kses_post($answer),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1)
          ));

          if (is_wp_error($post_id)) {
            $errors[] = "Failed to update existing FAQ #$index: " . $post_id->get_error_message();
          } else {
            $updated_count++;
          }
        } elseif ($skip_duplicates) {
          $skipped_count++;
        } else {
          // Import as duplicate
          $post_id = wp_insert_post(array(
            'post_title' => sanitize_text_field($question),
            'post_content' => wp_kses_post($answer),
            'post_type' => 'faqs',
            'post_status' => 'publish'
          ));

          if (is_wp_error($post_id)) {
            $errors[] = "Failed to import FAQ #$index: " . $post_id->get_error_message();
          } else {
            $imported_count++;
          }
        }
      } else {
        // Create new FAQ
        $post_id = wp_insert_post(array(
          'post_title' => sanitize_text_field($question),
          'post_content' => wp_kses_post($answer),
          'post_type' => 'faqs',
          'post_status' => 'publish'
        ));

        if (is_wp_error($post_id)) {
          $errors[] = "Failed to import FAQ #$index: " . $post_id->get_error_message();
        } else {
          $imported_count++;
          // Add to existing titles to prevent duplicates within the same import
          $existing_titles[$question_lower] = $post_id;
        }
      }
    }

    // Build result message
    $message_parts = array();
    
    if ($imported_count > 0) {
      $message_parts[] = "<strong>$imported_count</strong> FAQ(s) imported successfully";
    }
    
    if ($updated_count > 0) {
      $message_parts[] = "<strong>$updated_count</strong> FAQ(s) updated";
    }
    
    if ($skipped_count > 0) {
      $message_parts[] = "<strong>$skipped_count</strong> duplicate(s) skipped";
    }

    $total_processed = $imported_count + $updated_count + $skipped_count + count($errors);
    
    if (!empty($message_parts)) {
      $message = "Import completed: " . implode(', ', $message_parts) . ".";
    } else {
      $message = "No FAQs were processed.";
    }

    if (!empty($errors)) {
      $message .= "<br><br><strong>Errors encountered:</strong><br>" . implode("<br>", array_slice($errors, 0, 10));
      if (count($errors) > 10) {
        $message .= "<br>... and " . (count($errors) - 10) . " more errors.";
      }
    }

    // Determine result type
    if ($imported_count > 0 || $updated_count > 0) {
      $type = !empty($errors) ? 'warning' : 'success';
    } elseif (!empty($errors)) {
      $type = 'error';
    } else {
      $type = 'info';
    }

    return array('type' => $type, 'message' => $message);
  }

  public function faqs_order_page()
  {
    ?>
    <div class="wrap">
      <h2>Sort FAQs</h2>
      <p>Simply drag the FAQ up or down and they will be saved in the order they appear here.</p>

      <?php
      // Get all FAQs ordered by menu_order, then title
      $faqs = new WP_Query(array(
        'post_type' => 'faqs',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'menu_order title',
        'post_status' => 'publish'
      ));

      if ($faqs->have_posts()): ?>
        <table class="wp-list-table widefat fixed posts" id="sortable-table">
          <thead>
            <tr>
              <th class="column-order">Order</th>
              <th class="column-thumbnail">Thumbnail</th>
              <th class="column-title">Title</th>
              <th class="column-excerpt">Excerpt</th>
            </tr>
          </thead>
          <tbody data-post-type="faqs">
            <?php while ($faqs->have_posts()): 
              $faqs->the_post(); ?>
              <tr id="post-<?php the_ID(); ?>">
                <td class="column-order">
                  <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/icons/move.png'); ?>" 
                       title="Drag to reorder" alt="Move FAQ" width="30" height="30" class="move-handle" />
                </td>
                <td class="column-thumbnail">
                  <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('faq_logo'); ?>
                  <?php else: ?>
                    <span class="dashicons dashicons-format-chat" style="font-size: 40px; color: #ccc;"></span>
                  <?php endif; ?>
                </td>
                <td class="column-title">
                  <strong><?php the_title(); ?></strong>
                  <div class="row-actions">
                    <span class="edit">
                      <a href="<?php echo esc_url(get_edit_post_link()); ?>">Edit</a> | 
                    </span>
                    <span class="view">
                      <a href="<?php echo esc_url(get_permalink()); ?>" target="_blank">View</a>
                    </span>
                  </div>
                </td>
                <td class="column-excerpt">
                  <?php 
                  $excerpt = get_the_excerpt();
                  echo $excerpt ? esc_html(wp_trim_words($excerpt, 20)) : '<em>No excerpt available</em>';
                  ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
          <tfoot>
            <tr>
              <th class="column-order">Order</th>
              <th class="column-thumbnail">Thumbnail</th>
              <th class="column-title">Title</th>
              <th class="column-excerpt">Excerpt</th>
            </tr>
          </tfoot>
        </table>

        <style>
          .move-handle {
            cursor: move;
          }
          .ui-sortable-helper {
            background: #f9f9f9;
            border: 1px dashed #ccc;
          }
          .column-order {
            width: 60px;
            text-align: center;
          }
          .column-thumbnail {
            width: 80px;
            text-align: center;
          }
          .column-title {
            width: auto;
          }
          .column-excerpt {
            width: 300px;
          }
        </style>

      <?php else: ?>
        <div class="notice notice-info">
          <p>No FAQs found. <a href="<?php echo esc_url(admin_url('post-new.php?post_type=faqs')); ?>">Add your first FAQ</a> to get started!</p>
        </div>
      <?php endif; ?>

      <?php wp_reset_postdata(); ?>
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
