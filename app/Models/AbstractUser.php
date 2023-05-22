<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\TwoFactorAuthenticatable;
use MinVWS\Logging\Laravel\Contracts\LoggableUser;
use Kyslik\ColumnSortable\Sortable;

/**
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $uzi_serial
 * @property bool $active
 * @property array|null $roles
 * @property array $availableRoles
 * @property array $availableSuperRoles
 * @property Carbon $downloaded_at
 * @property string $uuid
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @method static Builder|AbstractUser query()
 * @method static Builder|AbstractUser sortable($defaultParameters = null)
 * @mixin \Eloquent
 */
abstract class AbstractUser extends Model implements UserInterface, LoggableUser
{
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Sortable;
    use HasAttributes;

    /**
     * The QR prefix for QR codes generated for users of this class
     */
    protected const QR_PREFIX = "";

    /** @var array */
    public static $availableRoles = [];

    /** @var array */
    public static $availableSuperRoles = [];

    /**
     * @var string
     */
    protected $table = null;

    /**
     * The website which is printed on PDFs generated for users of this class
     */
    public const SITE = '';

    /**
     * route prefix used
     */
    public string $route = '';


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
    ];

    /** @var string[] */
    public $sortable = [
        'email',
        'name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Attributes to be encrypted with sodium secretbox at rest.
     *
     * @var string[]
     */
    protected $encrypted = [
        'name',
    ];

    /**
     * Get the two factor authentication QR code URL.
     * We don't use full email but only the part before the @.
     *
     * @return string
     */
    public function twoFactorQrCodeUrl(): string
    {
        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(
            self::QR_PREFIX . (App::environment() == "production" ? "" : "-" . App::environment()),
            substr($this->email, 0, (int)strpos($this->email, '@')),
            decrypt((string)$this->two_factor_secret)
        );
    }

    /**
     * @return HasMany
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(static::class, 'created_by');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(static::class, 'created_by');
    }

    // Returns the class where credentials for this user are stored
    public static function getCredentialClass(): ?string
    {
        return null;
    }

    /**
     * @return ?HasMany
     */
    public function users(): ?HasMany
    {
        return $this->hasMany(static::class, 'created_by');
    }

    public function roles(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value ?? '[]') ?? [],
        );
    }

    /**
     * @param array|string $wantedRoles
     * @return bool
     */
    public function hasRole(array|string $wantedRoles): bool
    {
        $havingRoles = is_array($this->roles) ? $this->roles : [];

        if (is_string($wantedRoles)) {
            $wantedRoles = [ $wantedRoles ];
        }

        // Make sure all are in uppercase
        $wantedRoles = array_map('strtoupper', $wantedRoles);
        return count(array_intersect($wantedRoles, $havingRoles)) >= 1;
    }
}
