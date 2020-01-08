<?php $__env->startSection('content'); ?>
  <?php while(have_posts()): ?> <?php the_post() ?>
<div class="container blog-container">
    <div class="blog-post">
       <?php echo $__env->make('partials.content-single-'.get_post_type(), array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
       <?php endwhile; ?>
       <?php wp_reset_postdata(); ?>
    </div>
  <div class="sidebar">
<h2 class="sidebar-header">Past Posts</h2>
    <?php $catquery = new WP_Query( 'posts_per_page=5' ); ?>
    <ul>
      <?php while($catquery->have_posts()) : $catquery->the_post(); ?>
      <li><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></li>
      <?php endwhile;
    wp_reset_postdata();
      ?>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>