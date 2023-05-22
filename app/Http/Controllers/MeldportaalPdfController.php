<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;

class MeldportaalPdfController extends AbstractPdfController
{
    protected string $userClass = User::class;

    protected string $route = "meldportaal";
}
