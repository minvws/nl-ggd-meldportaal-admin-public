<?php

declare(strict_types=1);

namespace App\Models;

/*
 * Generic user interface for all user implementations
 */

interface UserInterface
{
    public static function getCredentialClass(): ?string;
}
