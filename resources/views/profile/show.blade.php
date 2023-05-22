@extends('layouts.app')

@section('content')
    <article>
        <div>
            @if(Auth::user()->password_updated_at !== null)
            <h1>{{ __('Update Password') }}</h1>
            @else
            <h1>{{ __('Activate account') }}</h1>

            <p>{{ __('Choose a new strong password to activate you account.') }}</p>
            <p>{{ __('Tip: a good password is long, easy to remember & type, but hard to guess. For example: unless someone who knows you well could guess it, &#34;quantum danger banana bread puppy&#34; is a good password.') }}</p>
            @endif

            <p>{{ __('Your password must adhere to the following rules:') }}</p>
            <ul>
                <li>{{ __('It must be at least 14 characters long.') }}</li>
                <li>{{ __('Cannot be similar to your name or email address.') }}</li>
                <li>{{ __('Is not a &#34;common&#34; password.') }}</li>
                <li>{{ __('Cannot contain only numerics.') }}</li>
            </ul>

            <form
                class="horizontal-view"
                method="post"
                action="{{ route('profile.update_password') }}"
            >
                @csrf
                <div>
                    <label for="current_password">{{ __('Current Password') }}</label>
                    <div>
                        <x-required />
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            required
                            autocomplete="current_password"
                            aria-describedby="current_password_error"
                        >
                        <x-input-error for="current_password" id="current_password_error" />
                    </div>
                </div>

                <div>
                    <label for="password">{{ __('New Password') }}</label>
                    <div>
                        <x-required />
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="password"
                            aria-describedby="password_error"
                        >
                        <x-input-error for="password" id="password_error" />
                    </div>
                </div>

                <div>
                     <label for="password_confirmation">{{ __('Confirm Password') }}</label>
                     <div>
                         <x-required />
                         <input
                             id="password_confirmation"
                             name="password_confirmation"
                             type="password"
                             required
                             autocomplete="password_confirmation"
                             aria-describedby="password_confirmation_error"
                         >
                        <x-input-error for="password_confirmation" id="password_confirmation_error" />
                    </div>
                </div>
                <button>{{ __('Save') }}</button>
            </form>
        </div>
    </article>

@endsection
