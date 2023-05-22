@extends('layouts.app')

@section('content')

<article>
    <div>
        <h1>{{ strtoupper($route) }} {{__('Users')}}</h1>
        <div class="actions">
            <a class="button" href="{{ route("users.${route}.create")}}">{{__('Create new user')}}</a>
        </div>

        <br>
        <section class="filter">
            <div>
                <button aria-expanded="false" data-show-filters-label="{{__('Show filters')}}">@lang('Show filters')</button>
            </div>

            <form action="" method="POST">
                @csrf
                <label for="filter">{{__('Keyword')}} <br><small>{{__('You can filter on name, email or roles like "ADMIN" and "REGISTRATOR"')}}</small></label>
                <input id="filter" name="filter" placeholder="{{__('E.g search by username')}}" type="text" value="{{Request::get('filter')}}">

                <div>
                    <label><input type="checkbox" checked name="filter_active" />@lang('Show active users')</label>
                    <label><input type="checkbox" checked name="filter_inactive" />@lang('Show inactive users')</label>
                </div>
                <button type="submit">{{__("Filter")}}</button>
            </form>
        </section>

        <div class="horizontal-scroll">

@if (count($users))
            <table>
                <caption>@lang('User overview'):</caption>
                <thead>
                  <tr>
                    <th scope="col" nowrap> @sortablelink('name', __('Name')) </th>
                    <th scope="col" nowrap> @sortablelink('email', __('E-mail')) </th>
                    <th scope="col">{{__('Roles')}}</th>
                    <th scope="col">{{__('Action')}}</th>
                  </tr>
                </thead>
                <tbody>
@foreach ($users as $user)
                  <tr>
                    <td>
                        {{ $user->name }}

                        @if (isset($user->active) && !$user->active)
                            <br><p class="de-emphasized">({{__('deactivated')}})</p>
                        @endif

                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @foreach ($user->roles as $role)
                            <span>{{ $role }}</span>
                        @endforeach
                    </td>
                    <td nowrap>
                        @if (Auth::user()->id != $user->id)
                            <a href="{{ route("users.${route}.edit", ['id' => $user->id])}}"><span class="ro-icon ro-icon-user"></span> {{__('Modify')}}</a>
                        @endif
                    </td>
                  </tr>
@endforeach
                </tbody>
            </table>
        </div>

        {!! $users->appends(\Request::except('page', '_token'))->render() !!}

@else
            <p class="system-notification" role="group" aria-label="{{__('system message') }}">
                {{ __('No users found') }}
            </p>
@endif

    </div></article>
@endsection
