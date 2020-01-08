<?php $__env->startSection('content'); ?>

<?php $backgroundImg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); ?>

<div class="hero" <?php if($backgroundImg)?>style="background: url('<?php echo $backgroundImg[0] ?>')">
        <h1 class="hero__text"><?php the_field('banner_text') ?></h1>
</div>

<div class="section-about">
    <div class="section-about__left">
        <h2><?php the_field('second_section_header') ?></h2>
        <p><?php the_field('second_section_paragraph') ?></p>
    </div>
    <div class="section-about__right">
        <div class="section-about__right--heading">Most Recent Torah Portion</div>
        <div class="section-about__right--content">
        <?php $catquery = new WP_Query( 'cat=21&posts_per_page=1' ); ?>
          <?php while($catquery->have_posts()) : $catquery->the_post(); ?>
          <div class="ctaButton"><a href="<?php the_permalink() ?>" class="ctaButton"><span>Go to Torah Portion</a></div>
          
          <?php endwhile;
        wp_reset_postdata();
          ?>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>