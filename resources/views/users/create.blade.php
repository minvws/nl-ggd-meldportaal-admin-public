@extends('layouts.app')

@section('content')

<article>
    <div>
        <h1>{{ strtoupper($route) }}: {{__('Create new user')}}</h1>
        <form action="{{ route("users.${route}.store") }}" method="post" class="horizontal-view">
            @csrf
            <fieldset>
                <legend>{{__('User details')}}</legend>

                <div>
                    <label for="name">{{__('Name')}}</label>
                    <input id="name" name="name" placeholder="Jan de Vries" type="text" value="{{ old('name') }}">
                    <x-input-error for="name" id="name_error" />
                </div>
                <div>
                    <label for="email">{{__('Email')}}</label>
                    <input id="email" name="email" placeholder="jandevries@email.nl" type="email" value="{{ old('email') }}">
                    <x-input-error for="email" id="email_error" />
                </div>
            </fieldset>

            <fieldset>
                <legend>{{__('Roles')}}</legend>
                <x-input-error for="roles" id="role_error" />

                <x-roles :disabled=false :availableRoles="$availableRoles" />
            </fieldset>

            <button type="submit">{{__('Send')}}</button>
        </form>
    </div>
</article>
@endsection
