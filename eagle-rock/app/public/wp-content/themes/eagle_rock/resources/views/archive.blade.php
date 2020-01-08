{{-- PAGE GETS POSTS FROM CATEGORY --}}

@extends('layouts.app')

@section('content')
<div class="container section-container">
    <div class="archive-page">
        <div class="posts">
        <div class="archive-page__header"><?php single_cat_title() ?> Posts</div>
            @while (have_posts()) @php the_post() @endphp
            @include('partials.content-'.get_post_type())
            @endwhile
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

  {!! get_the_posts_navigation() !!}
@endsection
