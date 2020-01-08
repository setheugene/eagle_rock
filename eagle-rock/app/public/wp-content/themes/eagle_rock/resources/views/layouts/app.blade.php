<!doctype html>
<html @php language_attributes() @endphp>
@include('partials.head')
@php do_action('get_header') @endphp
@include('partials.header')
<body @php body_class() @endphp>

    @yield('content')

    @php do_action('get_footer') @endphp
    @include('partials.footer')
    @php wp_footer() @endphp

</body>
</html>
