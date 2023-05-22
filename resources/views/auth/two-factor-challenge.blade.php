@extends('layouts.guest')

@section('content')
<section class="auth">
    <div>
        <h1>{{ __('Login to application') }} {{ config('app.mode') }}</h1>

        <p>
            {{ __('Enter the authentication code provided by your authenticator application and press \'Login\'.') }}
        </p>

        <form
            class="horizontal-view"
            method="POST"
            action="/two-factor-challenge"
            autocomplete="off"
        >
            @csrf

            @if ($errors->any())
                <p class="error" role="group" aria-label="foutmelding">
                    <span>@lang('Foutmelding:')</span> @lang($errors->first())
                </p>
            @endif

            <div>
                <label for="code">{{ __('Code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" required autofocus autocomplete="off" x-ref="code" />
            </div>

            <x-button>{{ __('Login') }}</x-button>
        </form>
    </div>
</section>
@endsection
