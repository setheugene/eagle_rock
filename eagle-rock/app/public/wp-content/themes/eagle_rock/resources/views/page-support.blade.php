@extends('layouts.app')

@section('content')

<div class="support-tithe">
    <div class="support-tithe__left">
        <h2><?php the_field('support_header') ?></h2>
        <div class="support-tithe__paragraph"><?php the_field('support_paragraph') ?></div>
    </div>
        <?php echo do_shortcode("[churchtithewp]"); ?>
</div>

@endsection()