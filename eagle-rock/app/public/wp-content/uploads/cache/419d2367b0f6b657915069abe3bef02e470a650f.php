<?php $__env->startSection('content'); ?>
<div class="container contact-us">
    <div class="contact-us__left">
        <div class="contact-us__left--header"><?php the_field('header'); ?></div>
        <div class="contact-us__left--paragraph"><?php the_field('paragraph'); ?></div>
        <div class="ctaButton"><a href="/about" class="ctaButton"><span>Learn More About Us</a></div>
    </div>
    <div class="contact-us__right">
  <?php while(have_posts()): ?> <?php the_post() ?>
    <?php echo $__env->make('partials.content-page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
  <?php endwhile; ?>
</div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>