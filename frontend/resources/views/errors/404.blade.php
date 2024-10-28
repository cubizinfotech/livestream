@extends("layouts.app")

@section("css")
<!-- Google font -->
<link href="https://fonts.googleapis.com/css?family=Montserrat:700,900" rel="stylesheet">
<!-- Custom stlylesheet -->
<link type="text/css" rel="stylesheet" href="{{ asset('assets') }}/css/404/style.css" />
@endsection

@section("content")
<div id="notfound">
    <div class="notfound">
        <div class="notfound-404">
            <h1>404</h1>
            <h2>Page not found</h2>
        </div>
        <a href="{{ url('/') }}">Homepage</a>
    </div>
</div>
@endsection

@section("scripts")
@endsection