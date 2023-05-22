@extends('layouts.guest')

@section('content')
    <section class="auth">
        <div>
            <div class="warning" role="group" aria-label="{{__('Warning')}}">
                <span>@lang("Warning"):</span>
                <h1>@yield('title')</h1>
                <p>@yield('message')</p>
            </div>
        </div>
    </section>
@endsection
