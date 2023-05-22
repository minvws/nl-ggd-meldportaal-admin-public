<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait HasAdminCreatedByField
 * @package App\Models\Traits
 * @author Rick Lambrechts <rick@rl-webdiensten.nl>
 */
trait HasAdminCreatedByField
{
    protected static function bootHasAdminCreatedByField(): void
    {
        static::creating(function ($model) {
            if (empty($model->admin_created_by)) {
                $model->admin_created_by = Auth::id();
            }
        });
    }
}
