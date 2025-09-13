<?php
/**
 * The template for displaying FAQ Archive pages.
 *
 * @package KwikFAQs
 *
 * @since KwikFAQs 1.0
 */
get_header(); ?>
<main id="primary" class="site-content">
  <div class="inner">
    <?php if (have_posts()): ?>
      <header class="page-header">
        <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        <?php the_archive_description('<div class="taxonomy-description">', '</div>'); ?>
      </header><!-- .page-header -->

      <div class="faqs-accordion">
        <?php
        while (have_posts()):
          the_post();
          ?>
          <div class="faq-item" id="faq-<?php the_ID(); ?>">
            <h2 class="faq-question">
              <a href="#faq-<?php the_ID(); ?>" class="faq-toggle">
                <?php the_title(); ?>
              </a>
            </h2>
            <div class="faq-answer">
              <?php the_content(); ?>
            </div>
          </div>
          <?php
        endwhile;
        ?>
      </div>

      <?php
      // Previous/next page navigation.
      the_posts_pagination(array(
        'prev_text' => __('Previous page', 'kwik'),
        'next_text' => __('Next page', 'kwik'),
        'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'kwik') . ' </span>',
      ));

    else:
      get_template_part('content', 'none');
    endif;
    ?>
  </div>
</main><!-- .site-content -->


<?php get_sidebar(); ?>
<?php get_footer(); ?>

