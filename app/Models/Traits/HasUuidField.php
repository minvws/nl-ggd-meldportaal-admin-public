<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Ramsey\Uuid\Uuid;

/**
 * Trait HasUuidField
 * @package App\Models\Traits
 * @author Pauline Vos <info@pauline-vos.nl>
 */
trait HasUuidField
{
    protected static function bootHasUuidField(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::uuid4()->toString();
            }
        });
    }
}
