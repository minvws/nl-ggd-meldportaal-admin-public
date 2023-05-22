<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\UserException;
use App\Models\User;
use App\Role;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * @extends AbstractUserController<User>
 */
class ApiUserController extends AbstractUserController
{
    protected string $userClass = User::class;

    protected string $route = "api";
    protected string $templatePrefix = "api";

    /**
     * @return Application|Factory|View
     */
    public function overview(Request $request)
    {
        $builder = $this->userClass::query()->whereJsonContains('roles', Role::API);

        /** @var string|null $filter */
        $filter = $request->get('filter');
        $active = filter_var($request->get('filter_active'), FILTER_VALIDATE_BOOLEAN);
        $inactive = filter_var($request->get('filter_inactive'), FILTER_VALIDATE_BOOLEAN);
        $builder = $this->addSearchFilter($builder, $filter ?? '', $active, $inactive);

        $pagelength = $request->get('pagelength', 50);
        $apiUsers = $builder->paginate((int)$pagelength);

        return view($this->templatePrefix . '.overview')
            ->with('route', $this->route)
            ->with('users', $apiUsers)
        ;
    }

    /**
     * @psalm-suppress InvalidPropertyFetch
     * @param Request $request
     *
     * @return Application|Factory|View|RedirectResponse
     */
    public function store(Request $request)
    {
        $input = $request->all();
        Validator::make($input, [
            'name' => [
                'required',
                'string'
            ],
            'serial' => [
                'required',
                'string',
            ],
        ])->validate();


        try {
            $request->merge([
                'email' => $request->get('serial') . '@uzi.ura',
                'roles' => [Role::API],
            ]);

            list($user) = $this->userRequestService->createNewUserFromRequest(
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
        ;
    }


    public function addSearchFilter(Builder $builder, string $filter, bool $active, bool $inactive): Builder
    {
        $builder
            ->select('mp_users.*')
        ;

        if ($active && !$inactive) {
            $builder->where('mp_users.active', true);
        }
        if ($inactive && !$active) {
            $builder->where('mp_users.active', false);
        }

        if (!empty($filter)) {
            $builder->where(function ($builder) use ($filter) {
                $builder->where('mp_users.email', 'LIKE', '%' . $filter . '%')
                    ->orWhereRaw('LOWER("mp_users"."name") LIKE \'%' . strtolower($filter) . '%\'')
                    ->orWhereJsonContains('roles', strtoupper($filter))
                    ->orWhereRaw('LOWER("mp_users"."uzi_serial") LIKE \'%' . strtolower($filter) . '%\'')
                ;
            });
        }

        return $builder;
    }

    protected function isAllowedToEdit(User $user): bool
    {
        return $user->hasRole(Role::API);
    }
}
