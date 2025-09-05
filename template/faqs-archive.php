<?php
/**
 * The template for displaying FAQ Archive pages.
 *
 * @package KwikFAQs
 * @since KwikFAQs 1.0
 */
get_header(); ?>

  <section id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

    <?php if ( have_posts() ) : ?>

      <header class="page-header">
        <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        <?php
          the_archive_description( '<div class="taxonomy-description">', '</div>' );
        ?>
      </header><!-- .page-header -->

      <div class="faqs-accordion">
      <?php
      while ( have_posts() ) : the_post();
      ?>
        <div class="faq-item" id="faq-<?php the_ID(); ?>">
          <h2 class="faq-question">
            <a href="#faq-<?php the_ID(); ?>" class="faq-toggle">
              <?php the_title(); ?>
            </a>
          </h2>
          <div class="faq-answer entry-content">
            <?php the_content(); ?>
          </div>
        </div>
      <?php
      endwhile;
      ?>
      </div>

      <?php
      // Previous/next page navigation.
      the_posts_pagination( array(
        'prev_text'          => __( 'Previous page', 'kwik' ),
        'next_text'          => __( 'Next page', 'kwik' ),
        'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'kwik' ) . ' </span>',
      ) );

    // If no content, include the "No posts found" template.
    else :
      get_template_part( 'content', 'none' );
    endif;
    ?>

    </main><!-- .site-main -->
  </section><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
