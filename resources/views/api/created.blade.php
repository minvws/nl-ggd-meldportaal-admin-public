@extends('layouts.app')

@section('content')
    <article>
        <div>
            <h1>{{__('New API user')}}</h1>
            <dl>
                <div>
                    <dt>{{__('Name')}}</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div>
                    <dt>{{__('Serial')}}</dt>
                    <dd>{{ $user->uzi_serial }}</dd>
                </div>
            </dl>
        </div>
    </article>

@endsection
