<?php

declare(strict_types=1);

namespace App;

/**
 * Class that defines roles for users to restrict functionality.
 *
 * @package App
 */
final class Role
{
    // Users that are able to administer ALL users in the admin portal (cannot login into the meldportaal though)
    public const SUPER_ADMIN = "SUPER_ADMIN";

    // Administrator users, or users that are authenticated with UZI passes as doctors
    public const USER_ADMIN = "USER_ADMIN";

    // Regular users (can not login into the user admin portal)
    public const USER = "USER";

    // Users that are able to use the API
    public const API = "API";

    // Will generate only specimen tests
    public const SPECIMEN = "SPECIMEN";

    /**
     * @var string[] List of all roles for easy filtering
     */
    public static $mapping = [
        Role::SUPER_ADMIN,
        Role::USER_ADMIN,
        Role::USER,
        Role::API,
        Role::SPECIMEN,
    ];

    /**
     * @var string[] List of all roles that are supporting and must be accompanied by at least one non-supporting role
     */
    public static $supportingRoles = [
        Role::SPECIMEN,
    ];

    /**
     * Fetches roles based on the HTML names. $roles in this case is taken directly from the HTTP request.
     *
     * @param array $roles
     * @return array
     */
    public static function parseFromRequest(array $roles): array
    {
        $out = [];

        foreach (self::$mapping as $v) {
            if (in_array($v, $roles)) {
                $out[] = $v;
            }
        }

        return $out;
    }

    public static function getSupportingRoles(): array
    {
        return self::$supportingRoles;
    }

    public static function isSupportingRole(string $role): bool
    {
        return in_array($role, self::$supportingRoles);
    }
}
