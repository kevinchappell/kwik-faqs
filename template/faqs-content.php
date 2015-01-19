<?php

/**
 * The default template for displaying FAQ content.
 *
 * @package KwikFAQs
 * @since KwikFAQs 1.0
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <header class="entry-header">
    <?php
      if ( is_single() ) :
        the_title( '<h1 class="entry-title">', '</h1>' );
      else :
        the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
      endif;
    ?>
  </header><!-- .entry-header -->

  <div class="entry-content">
    <?php

    if(is_home()){
      the_excerpt();
    } else {
      the_content( sprintf(
        __( 'Continue reading %s', 'kwik' ),
        the_title( '<span class="screen-reader-text">', '</span>', false )
      ) );
    }

      wp_link_pages( array(
        'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'kwik' ) . '</span>',
        'after'       => '</div>',
        'link_before' => '<span>',
        'link_after'  => '</span>',
        'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'kwik' ) . ' </span>%',
        'separator'   => '<span class="screen-reader-text">, </span>',
      ) );
    ?>
  </div><!-- .entry-content -->

  <footer class="entry-footer">
    <?php kt_entry_meta(); ?>
  </footer><!-- .entry-footer -->

</article><!-- #post-## -->
