@extends('layouts.guest')

@section('content')
    <section class="auth">
        <div>
            <div class="warning" role="group" aria-label="{{__('Warning')}}">
                <span>@lang("Warning"):</span>
                <h1>{{$title}}</h1>
                <p>{{__("Something went wrong. Please contact our support desk")}}</p>

                <span class="de-emphasized">({{now()}})</span>
            </div>
        </div>
    </section>
@endsection
