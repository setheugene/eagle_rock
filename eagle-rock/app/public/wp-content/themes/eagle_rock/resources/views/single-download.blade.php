@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
<div class="container single-product single-product-container">
    <div class="single-product__left">
        <?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' ); ?>
        <img src="<?php echo $url ?>" />
    </div>
  <div class="single-product__right">
    @include('partials.content-single-'.get_post_type())
    @endwhile
</div>
</div>
<?php wp_reset_postdata(); ?>
@endsection
