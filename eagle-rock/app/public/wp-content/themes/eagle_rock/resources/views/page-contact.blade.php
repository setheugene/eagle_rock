@extends('layouts.app')

@section('content')
<div class="container contact-us">
    <div class="contact-us__left">
        <div class="contact-us__left--header"><?php the_field('header'); ?></div>
        <div class="contact-us__left--paragraph"><?php the_field('paragraph'); ?></div>
        <div class="ctaButton"><a href="/about" class="ctaButton"><span>Learn More About Us</a></div>
    </div>
    <div class="contact-us__right">
  @while(have_posts()) @php the_post() @endphp
    @include('partials.content-page')
  @endwhile
</div>
</div>
@endsection