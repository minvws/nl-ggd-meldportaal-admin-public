<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Casts\SmimeCast;
use App\Models\Traits\Encrypted;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserCredential
 *
 * Represents an credentials entry from which we can generate PDFs
 *
 * @mixin \Eloquent
 * @property string $id
 * @property SmimeCast $twofa_url
 * @property SmimeCast $password
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|UserCredential newModelQuery()
 * @method static Builder|UserCredential newQuery()
 * @method static Builder|UserCredential query()
 * @method static Builder|UserCredential wheretwofaUrl($value)
 * @method static Builder|UserCredential whereCreatedAt($value)
 * @method static Builder|UserCredential whereId($value)
 * @method static Builder|UserCredential wherePassword($value)
 * @method static Builder|UserCredential whereUpdatedAt($value)
 * @method static Builder|UserCredential whereUserId($value)
 */
class UserCredential extends Model
{
    use HasUuid;
    use Encrypted;

    /**
     * @var string
     */
    protected $table = 'mp_credentials';

    /**
     * @var string
     */
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'twofa_url',
        'password',
        'user_id',
    ];

    /**
     * The attributes that are encrypted
     *
     * @var array
     */
    protected $encrypted = [
        'twofa_url',
        'password',
    ];
}
