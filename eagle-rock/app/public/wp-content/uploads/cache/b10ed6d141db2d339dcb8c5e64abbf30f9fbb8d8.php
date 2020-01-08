<?php $__env->startSection('content'); ?>
<div class="container section-container">
    <div class="archive-page">
        <div class="posts">
        <div class="archive-page__header"><?php single_cat_title() ?> Posts</div>
            <?php while(have_posts()): ?> <?php the_post() ?>
            <?php echo $__env->make('partials.content-'.get_post_type(), array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            <?php endwhile; ?>
        </div>
        <div class="sidebar">
          <h2 class="sidebar-header">Categories</h2>
          <ul>
            <li><a href="/blog">All</a></li>
              <?php 
              wp_list_categories([
                'title_li'=> __( '' ),
              ]);
              wp_reset_postdata();
                ?>
                </ul>
      </div>
    </div>
</div>

  <?php echo get_the_posts_navigation(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>