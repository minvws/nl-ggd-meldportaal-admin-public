@if (in_array(\App\Role::SUPER_ADMIN, $attributes->get('availableRoles')))
    <div>
        <input @if ($disabled) disabled @endif type="checkbox" id="super_admin_role" value="{{\App\Role::SUPER_ADMIN}}" name="roles[]" @if (isset($user) && $user->hasRole(\App\Role::SUPER_ADMIN)) checked @endif />
        <label for="super_admin_role">{{__('Has role: Super Administrator')}} @if (\App\Role::isSupportingRole(\App\Role::SUPER_ADMIN))<small>({{__("supporting")}})</small>@endif</label>
        <span class="nota-bene">{{__('This user can manage ALL users')}}</span>
    </div>
@endif

@if (in_array(\App\Role::USER_ADMIN, $attributes->get('availableRoles')))
    <div>
        <input @if ($disabled) disabled @endif type="checkbox" id="user_admin_role" value="{{\App\Role::USER_ADMIN}}" name="roles[]" @if (isset($user) && $user->hasRole(\App\Role::USER_ADMIN)) checked @endif />
        <label for="user_admin_role">{{__('Has role: User Administrator')}} @if (\App\Role::isSupportingRole(\App\Role::USER_ADMIN))<small>({{__("supporting")}})</small>@endif</label>
        <span class="nota-bene">{{__('This user can create other users')}}</span>
    </div>
@endif

@if (in_array(\App\Role::USER, $attributes->get('availableRoles')))
    <div>
        <input @if ($disabled) disabled @endif type="checkbox" id="user_role" value="{{\App\Role::USER}}" name="roles[]" @if (isset($user) && $user->hasRole(\App\Role::USER)) checked @endif />
        <label for="user_role">{{__('Has role: User')}} @if (\App\Role::isSupportingRole(\App\Role::USER))<small>({{__("user")}})</small>@endif</label>
        <span class="nota-bene">{{__('This user can only use the reporting portal')}}</span>
    </div>
@endif

@if (in_array(\App\Role::API, $attributes->get('availableRoles')))
    <div>
        <input @if ($disabled) disabled @endif type="checkbox" id="api_role" value="{{\App\Role::API}}" name="roles[]" @if (isset($user) && $user->hasRole(\App\Role::API)) checked @endif />
        <label for="api_role">{{__('Has role: API')}} @if (\App\Role::isSupportingRole(\App\Role::API))<small>({{__("supporting")}})</small>@endif</label>
        <span class="nota-bene">{{__('This user can use the API')}}</span>
    </div>
@endif

@if (in_array(\App\Role::SPECIMEN, $attributes->get('availableRoles')))
    <div>
        <input @if ($disabled) disabled @endif type="checkbox" id="specimen_role" value="{{\App\Role::SPECIMEN}}" name="roles[]" @if (isset($user) && $user->hasRole(\App\Role::SPECIMEN)) checked @endif />
        <label for="specimen_role">{{__('Has role: SPECIMEN')}} @if (\App\Role::isSupportingRole(\App\Role::SPECIMEN))<small>({{__("supporting")}})</small>@endif</label>
        <span class="nota-bene">{{__('This user can only generate specimen tests')}}</span>
    </div>
@endif
