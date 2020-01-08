<?php $__env->startSection('content'); ?>
<div class="container section-container">
    <div class="blog-page">
        <div class="posts">
        <div class="blog-page__header">All Posts</div>
        <?php $catquery = new WP_Query( 'posts_per_page=5' ); ?>
        <ul>
          <?php while($catquery->have_posts()) : $catquery->the_post(); ?>
          <li><a href="<?php the_permalink() ?>" rel="bookmark"><h2><?php the_title(); ?></h2></a>
        <div class="entry-summary"><?php the_excerpt()?></div></li>
          <?php endwhile;
        wp_reset_postdata();
          ?>
          
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