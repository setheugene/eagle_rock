<?php $__env->startSection('content'); ?>
<div class="container shop-container">
    <div class="shop-page">
        <h1 class="shop-page__header">Available Downloads</h1>
        
        <div class="products"> 
            <?php while(have_posts()): ?> <?php the_post() ?>
            <div class="individual-product">
            <div><?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' ); ?></div>
            {{-- <img src="<?php echo $url ?>" /> --}} <div class="image"></div>
            <h4><a href="<?php echo the_permalink(); ?>"><?php the_title(); ?></a></h4>
        </div>
            <?php endwhile; ?>
            
        </div>
    </div>
</div>

  <?php echo get_the_posts_navigation(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>