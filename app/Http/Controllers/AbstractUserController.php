<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\UserException;
use App\Models\AbstractUser;
use App\Models\User;
use App\Role;
use App\Services\UserGeneratorService;
use App\Services\UserRequestService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use MinVWS\Logging\Laravel\Events\Logging\AccountChangeLogEvent;
use MinVWS\Logging\Laravel\LogService;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Throwable;

/**
 * @template TUser of AbstractUser
 */
abstract class AbstractUserController extends BaseController
{
    protected string $route = "";

    /** @var class-string<TUser> */
    protected string $userClass;
    protected string $templatePrefix = "";


    protected UserGeneratorService $userGeneratorService;
    protected UserRequestService $userRequestService;
    protected LogService $logService;

    abstract public function addSearchFilter(Builder $builder, string $filter, bool $active, bool $inactive): Builder;

    public function __construct(
        UserGeneratorService $userGeneratorService,
        UserRequestService $userRequestService,
        LogService $logService
    ) {
        $this->userGeneratorService = $userGeneratorService;
        $this->userRequestService = $userRequestService;
        $this->logService = $logService;
    }

    /**
     * @return Application|Factory|View
     */
    public function overview(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole(Role::SUPER_ADMIN)) {
            $builder = $this->userClass::query()
                ->whereJsonDoesntContain('roles', 'API')
            ;
        } else {
            $builder = $this->userClass::where('mp_users.created_by', $user->id)
                ->whereJsonDoesntContain('roles', 'API')
            ;
        }

        /** @var string|null $filter */
        $filter = $request->get('filter');
        $active = filter_var($request->get('filter_active'), FILTER_VALIDATE_BOOLEAN);
        $inactive = filter_var($request->get('filter_inactive'), FILTER_VALIDATE_BOOLEAN);

        $builder = $this->addSearchFilter($builder, $filter ?? '', $active, $inactive);

        $pagelength = $request->get('pagelength', 50);
        $adminUsers = $builder->paginate((int)$pagelength);

        return view($this->templatePrefix . '.overview')
            ->with('route', $this->route)
            ->with('users', $adminUsers)
        ;
    }

    /**
     * @psalm-suppress InvalidPropertyFetch
     * @return Application|Factory|View|RedirectResponse
     */
    public function edit(string $id)
    {
        /** @var User $user */
        $user = $this->userClass::findOrFail($id);

        if (! $this->isAllowedToEdit($user)) {
            return redirect()->route('users.' . $this->route . '.overview');
        }

        return view($this->templatePrefix . '.edit')
            ->with('availableRoles', $this->findAvailableRoles())
            ->with('route', $this->route)
            ->with('user', $user)
        ;
    }

    /**
     * @psalm-suppress InvalidPropertyFetch
     * @return Application|Factory|View|RedirectResponse
     */
    public function view(string $id)
    {
        /** @var User $user */
        $user = $this->userClass::findOrFail($id);

        if (! $this->isAllowedToEdit($user)) {
            return redirect()->route('users.' . $this->route . '.overview');
        }

        return view($this->templatePrefix . '.view')
            ->with('availableRoles', $this->findAvailableRoles())
            ->with('route', $this->route)
            ->with('user', $user)
        ;
    }

    /**
     * @param Request $request
     * @param string $id
     *
     * @return Application|Factory|View|RedirectResponse
     *
     * @throws IncompatibleWithGoogleAuthenticatorException|InvalidCharactersException|SecretKeyTooShortException
     */
    public function reset(Request $request, string $id)
    {
        /** @var User $user */
        $user = $this->userClass::findOrFail($id);

        if (! $this->isAllowedToEdit($user)) {
            return redirect()->route('users.' . $this->route . '.overview');
        }

        $password = $this->userGeneratorService->resetCredentials(
            $user,
            $request->request->getBoolean('change_pwd', false),
            $request->request->getBoolean('change_2fa', false),
        );

        Session::flash('message', __('Login credentials have been reset'));
        Session::flash('class', 'confirmation');

        return view($this->templatePrefix . '.created')
            ->with('route', $this->route)
            ->with('user', $user)
            ->with('password', $password)
        ;
    }

    /**
     * @psalm-suppress InvalidPropertyFetch
     * @param Request $request
     *
     * @return Application|Factory|View|RedirectResponse
     *
     * @throws IncompatibleWithGoogleAuthenticatorException|InvalidCharactersException|SecretKeyTooShortException|Throwable
     */
    public function store(Request $request)
    {
        $input = $request->all();
        Validator::make($input, [
            'name' => [
                'required',
                'string'
            ],
            'email' => [
                'required',
                'email',
            ],
            'roles' => [
                'required',
                'array',
                'supporting_roles:' . join(',', Role::getSupportingRoles()),
            ],
            'roles.*' => Rule::in($this->findAvailableRoles()),
            'serial' => [
                'nullable',
            ],
        ], [
            'roles.supporting_roles' => __('You cannot create a user with only supporting roles'),
            'roles.*' => __('You can not create a user without roles'),
        ])->validate();


        try {
            list($user, $password) = $this->userRequestService->createNewUserFromRequest(
                $this->userClass,
                $request
            );
        } catch (UserException $exception) {
            Session::flash('message', $exception->getMessage());
            Session::flash('class', 'error');

            return back()->withInput();
        }

        $user->save();

        Session::flash('message', __('New user created'));
        Session::flash('class', 'confirmation');

        return view($this->templatePrefix . '.created')
            ->with('route', $this->route)
            ->with('user', $user)
            ->with('password', $password);
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        return view($this->templatePrefix . '.create')
            ->with('route', $this->route)
            ->with('availableRoles', $this->findAvailableRoles())
        ;
    }

    public function activate(string $id): RedirectResponse
    {
        /** @var User $user */
        $user = $this->userClass::findOrFail($id);

        if (! $this->isAllowedToEdit($user)) {
            return redirect()->route('users.' . $this->route . '.overview');
        }

        $this->activateUser($user, true);

        Session::flash('message', __('User activated'));
        Session::flash('class', 'confirmation');

        return back()->withInput();
    }

    /**
     * @return RedirectResponse
     */
    public function deactivate(string $id): RedirectResponse
    {
        /** @var User $user */
        $user = $this->userClass::findOrFail($id);

        if (! $this->isAllowedToEdit($user)) {
            return redirect()->route('users.' . $this->route . '.overview');
        }

        $this->activateUser($user, false);

        Session::flash('message', __('User deactivated'));
        Session::flash('class', 'confirmation');

        return back()->withInput();
    }

    /**
     * @throws ValidationException
     */
    public function roles(Request $request, string $id): RedirectResponse
    {
        $input = $request->all();
        Validator::make($input, [
            'roles' => ['required', 'min:1'],
        ], [
            'min' => __('Account should have at least one role'),
        ])->validate();

        /** @var AbstractUser $user */
        $user = $this->userClass::findOrFail($id);
        $user->roles = $request->get('roles');
        $user->save();

        Session::flash('message', __('Modified user roles'));
        Session::flash('class', 'confirmation');

        /** @var User $loggedInUser */
        $loggedInUser = Auth::user();
        $this->logService->log((new AccountChangeLogEvent())
            ->asUpdate()
            ->withActor($loggedInUser)
            ->withTarget($user)
            ->withSource(config('app.name'))
            ->withEventCode(AccountChangeLogEvent::EVENTCODE_ROLES)
            ->withData([
                'user_id' => $user->id,
                'last_active_at' => $loggedInUser->last_active_at,
                'last_login_at' => $loggedInUser->last_login_at,
                'roles' => $user->roles,
                'table' => (new $this->userClass())->getTable(),
            ]));

        return back()->withInput();
    }

    /**
     * @param Request $request
     * @param string $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function account(Request $request, string $id): RedirectResponse
    {
        $input = $request->all();
        Validator::make($input, [
            'account.name' => ['required', 'string', 'min:4'],
            'account.serial' => ['nullable', 'string'],
        ], [
            'min' => __('Name should be at least 4 chars'),
        ])->validate();

        /** @var AbstractUser $user */
        $user = $this->userClass::findOrFail($id);
        $user->name = $input['account']['name'];
        $user->uzi_serial = $input['account']['serial'] ?? '';

        $nameChanged = $user->isDirty('name');
        $serialChanged = $user->isDirty('serial');
        $user->save();

        /** @var User $loggedInUser */
        $loggedInUser = Auth::user();
        $this->logService->log((new AccountChangeLogEvent())
            ->asUpdate()
            ->withActor($loggedInUser)
            ->withTarget($user)
            ->withSource(config('app.name'))
            ->withEventCode(AccountChangeLogEvent::EVENTCODE_USERDATA)
            ->withData([
                'user_id' => $user->id,
                'last_active_at' => $loggedInUser->last_active_at,
                'last_login_at' => $loggedInUser->last_login_at,
                'name_changed' => $nameChanged,
                'serial_changed' => $serialChanged,
            ]));

        Session::flash('message', __('Account modified'));
        Session::flash('class', 'confirmation');

        return back()->withInput();
    }

    protected function activateUser(AbstractUser $user, bool $active): void
    {
        $user->active = $active;
        $user->save();

        /** @var User $loggedInUser */
        $loggedInUser = Auth::user();
        $this->logService->log((new AccountChangeLogEvent())
            ->asUpdate()
            ->withActor($loggedInUser)
            ->withTarget($user)
            ->withSource(config('app.name'))
            ->withEventCode(AccountChangeLogEvent::EVENTCODE_ACTIVE)
            ->withData([
                'user_id' => $user->id,
                'last_active_at' => $loggedInUser->last_active_at,
                'last_login_at' => $loggedInUser->last_login_at,
                'active' => $active,
                'table' => (new $this->userClass())->getTable(),
            ]));

        if ($active === false) {
            // Recursively deactivate all underlying users if this user created other users
            $createdUsers = $this->userClass::where('created_by', $user->id)->get();
            foreach ($createdUsers as $createdUser) {
                /** @var AbstractUser $createdUser */
                $this->activateUser($createdUser, false);
            }
        }
    }

    /**
     * Returns the roles allowed to be viewed/editted by the current user. This depends on the user's role.
     */
    protected function findAvailableRoles(): array
    {
        /** @var User $user */
        $user = Auth::user();

        $availableRoles = $this->userClass::$availableRoles ?? [];
        if ($user->hasRole(Role::SUPER_ADMIN)) {
            return $availableRoles;
        }

        return array_diff($availableRoles, $this->userClass::$availableSuperRoles ?? []);
    }

    protected function isAllowedToEdit(User $user): bool
    {
        return true;
    }
}
