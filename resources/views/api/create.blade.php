@extends('layouts.app')

@section('content')

<article>
    <div>
        <h1>{{ strtoupper($route) }}: {{__('Create new API user')}}</h1>
        <form action="{{ route("users.${route}.store") }}" method="post" class="horizontal-view">
            @csrf
            <fieldset>
                <legend>{{__('User details')}}</legend>

                <div>
                    <label for="name">{{__('Name')}}</label>
                    <input id="name" name="name" placeholder="DN" type="text" value="{{ old('name') }}">
                    <x-input-error for="name" id="name_error" />
                </div>
            </fieldset>

            <fieldset>
                <div>
                    <label for="serial">{{__('API serial')}}</label>
                    <input id="serial" name="serial" placeholder="12345678" type="text" value="{{ old('serial') }}">
                    <x-input-error for="serial" id="serial_error" />
                    <span class="nota-bene">{{__('The serial must match the serial number in the UZI server certificate of the user.')}}</span>
                </div>
            </fieldset>

            <button type="submit">{{__('Send')}}</button>
        </form>
    </div>
</article>
@endsection
