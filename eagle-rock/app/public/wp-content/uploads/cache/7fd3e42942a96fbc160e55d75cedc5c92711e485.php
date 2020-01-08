<article <?php post_class() ?>>
  <header>
    <h2 class="entry-title"><a href="<?php echo e(get_permalink()); ?>"><?php echo get_the_title(); ?></a></h2>
  </header>
  <div class="entry-summary">
    <?php the_excerpt() ?>
  </div>
</article>
