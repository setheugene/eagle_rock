<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="about-page">
      <div class="row1">
        <div class="paragraph"><strong>Who we are - </strong><?php the_field('who'); ?></div>
        <div class="who"></div>
      </div>
      <div class="row2">
        <div class="paragraph"><strong>What we are - </strong><?php the_field('what'); ?></div>
        <div class="what"></div>
      </div>
      <div class="row3">
        <div class="paragraph"><strong>Why we are - </strong><?php the_field('why'); ?></div>
        <div class="why"></div>
      </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>