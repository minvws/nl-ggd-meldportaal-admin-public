<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @extends AbstractUserController<User>
 */
class MeldportaalUserController extends AbstractUserController
{
    protected string $userClass = User::class;

    protected string $route = "meldportaal";

    protected string $templatePrefix = "users";

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
        return $user->hasRole(Role::API) === false;
    }
}
