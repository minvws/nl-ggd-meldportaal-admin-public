<?php

declare(strict_types=1);

namespace App\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Class LowerCast
 *
 * Convert the attribute to lowercase.
 *
 * @template-implements CastsAttributes<string, string>
 *
 * @author jthijssen@noxlogic.nl
 */
class LowerCast implements CastsAttributes
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed|string
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return strtolower($value);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed|string
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return strtolower($value);
    }
}
