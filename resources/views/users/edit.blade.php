@extends('layouts.app')

@section('content')

    <article>
        <div>

            <h1>{{__('Modify user')}} {{ $user->email }}</h1>

            @if ($errors->any())
                <div class="error">
                    {{__('An error occured while validating the data')}}
                </div>
            @endif

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
                                <a href="{{ route("users.${route}.edit", ['id' => $registrator->id])}}"><span
                                        class="ro-icon ro-icon-user"></span> {{__('Modify')}}</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif


            <form method=POST action={{ route("users.${route}.account", ['id' => $user->id])}} class="horizontal-view">
                @csrf
                <fieldset>
                    <legend>{{__('Data')}}</legend>
                    <div>
                        <label for="account.name">{{__('Name')}}</label>
                        <input id="account.name" name="account[name]" placeholder="" type="text"
                               value="{{ $user->name ?? '' }}">
                        <x-input-error for="account.name" id="account.name_error"/>
                    </div>
                </fieldset>
                <button type="submit">{{__('Update account data')}}</button>
            </form>

            <form method=POST action={{ route("users.${route}.roles", ['id' => $user->id])}} class="horizontal-view">
                @csrf
                <fieldset>
                    <legend>{{__('Roles')}}</legend>
                    <x-input-error for="roles" id="roles_error"/>

                    <x-roles :disabled=false :availableRoles="$availableRoles" :user=$user/>
                </fieldset>
                <button type="submit">{{__('Save roles')}}</button>
            </form>

            @if(isset($user->active))
                @if ($user->active)
                    <form method=POST
                          action={{ route("users.${route}.deactivate", ['id' => $user->id])}} class="horizontal-view">
                        @csrf
                        <fieldset>
                            <legend>{{__('Deactivate')}}</legend>
                            <div>
                                <button type="submit">{{__('Deactivate user')}}</button>
                            </div>
                        </fieldset>
                    </form>
                @else
                    <form method=POST
                          action={{ route("users.${route}.activate", ['id' => $user->id])}} class="horizontal-view">
                        @csrf
                        <fieldset>
                            <legend>{{__('Activate')}}</legend>
                            <div>
                                <button type="submit">{{__('Activate user')}}</button>
                            </div>
                        </fieldset>
                    </form>
                @endif
            @endif

            @if ($user->uzi_number == null)
                <form method="post"
                      action={{ route("users.${route}.reset_credentials", ['id' => $user->id])}} class="horizontal-view">

                    @csrf
                    <fieldset>
                        <legend>{{__('Reset login credentials')}}</legend>
                        <div>
                            <input type="checkbox" id="change_pwd" name="change_pwd">
                            <label for="change_pwd">{{__('I want to reset the password')}}</label>
                        </div>

                        <div>
                            <input type="checkbox" id="change_2fa" name="change_2fa">
                            <label for="change_2fa">{{__('I want to reset 2FA')}}</label>
                        </div>
                    </fieldset>

                    <button type="submit">{{__('Reset login data')}}</button>
                </form>
            @endif

        </div>
    </article>
@endsection
