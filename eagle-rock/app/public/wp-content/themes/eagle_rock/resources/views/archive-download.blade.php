
@extends('layouts.app')

@section('content')
<div class="container shop-container">
    <div class="shop-page">
        <h1 class="shop-page__header">Available Downloads</h1>
        {{-- <div class="top-bar">
            <h2 class="top-bar__left">Books</h2>
            <h2 class="top-bar__right">Videos</h2>
        </div> --}}
        <div class="products"> 
            @while (have_posts()) @php the_post() @endphp
            <div class="individual-product">
            <div><?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' ); ?></div>
            <img src="<?php echo $url ?>" />
            <h4><a href="<?php echo the_permalink(); ?>"><?php the_title(); ?></a></h4>
        </div>
            @endwhile
            
        </div>
    </div>
</div>

  {!! get_the_posts_navigation() !!}
@endsection
