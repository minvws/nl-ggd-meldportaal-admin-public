@extends('layouts.app')

@section('content')

    <article>
        <div>

            <h1>{{__('View user')}} {{ $user->email }}</h1>

            @if (isset($user->active) && !$user->active)
                <div class="warning">
                    {{__('This user is inactive')}}
                </div>
            @endif

            <table>
                <tr>
                    <td>{{__('Name')}}</td>
                    <td>{{$user->name}}</td>
                </tr>
                <tr>
                    <td>{{__('Email')}}</td>
                    <td>{{$user->email}}</td>
                </tr>
                <tr>
                    <td>{{__('Created at')}}</td>
                    <td>{{$user->created_at->format('Y-m-d H:i:s')}}</td>
                </tr>
                @if(isset($user->created_by))
                    <tr>
                        <td>{{__('Created by')}}</td>
                        <td>
                            @if ($user->createdBy)
                                <a href="{{ route("users.${route}.edit", ['id' => $user->createdBy->id])}}"><span
                                            class="ro-icon ro-icon-user"></span> {{ $user->createdBy->name }}
                                    ({{$user->createdBy->email}})</a>
                            @elseif($user->adminCreatedBy)
                                <span class="ro-icon ro-icon-user"></span> {{ $user->adminCreatedBy->name }}
                                ({{$user->adminCreatedBy->email}})
                            @endif
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>{{__('Last login')}}</td>
                    <td>{{$user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : __('not yet logged in')}}</td>
                </tr>
            </table>


            @if ($user->users() && $user->users !== null && count($user->users) > 0)
                <table>
                    <thead>
                    <tr>
                        <th>{{__('Name')}}</th>
                        <th>{{__('Email')}}</th>
                        <th>{{__('Edit')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($user->users as $registrator)
                        <tr>
                            <td>{{ $registrator->name }}</td>
                            <td>{{ $registrator->email }}</td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif


            <form class="horizontal-view">
                <fieldset>
                    <legend>{{__('Data')}}</legend>
                    <div>
                        <label for="account.name">{{__('Name')}}</label>
                        <input id="account.name" readonly name="account[name]" placeholder="" type="text"
                               value="{{ $user->name ?? '' }}">
                    </div>
                </fieldset>
            </form>

            <form method=POST action={{ route("users.${route}.roles", ['id' => $user->id])}} class="horizontal-view">
                @csrf
                <fieldset>
                    <legend>{{__('Roles')}}</legend>

                    <x-roles :disabled=true :availableRoles="$availableRoles" :user=$user/>
                </fieldset>
            </form>

        </div>
    </article>
@endsection
