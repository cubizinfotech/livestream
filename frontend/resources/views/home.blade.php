@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <center>
                        <p>{{ __('Welcome to Live Stream') }}</p>
                        <a href="{{ route('admin.home') }}" class="btn btn-outline-primary"><b>Live Stream</b></a>
                    </center>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
