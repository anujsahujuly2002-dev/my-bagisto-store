<?php

namespace EcomForYou\Tenancy\Models;

use EcomForYou\Tenancy\Contracts\Tenant as TenantContract;
use EcomForYou\Tenancy\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model implements TenantContract
{
    /**
     * The landlord connection every tenant record lives on.
     *
     * @var string
     */
    protected $connection = 'landlord';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenants';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'subdomain',
        'database',
        'status',
        'plan_id',
        'owner_email',
        'trial_ends_at',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * The attribute defaults of a new tenant.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => TenantStatus::PENDING_VERIFICATION,
    ];

    /**
     * Store the subdomain lowercased and trimmed, the only form identification looks up.
     */
    public function setSubdomainAttribute(?string $value): void
    {
        $this->attributes['subdomain'] = $value === null ? null : strtolower(trim($value));
    }

    /**
     * Store the owner email trimmed, and null in place of an empty string.
     */
    public function setOwnerEmailAttribute(?string $value): void
    {
        $value = $value === null ? '' : trim($value);

        $this->attributes['owner_email'] = $value === '' ? null : $value;
    }

    /**
     * The package the tenant is on.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanProxy::modelClass());
    }

    /**
     * The usage recorded for the tenant against its package's limits.
     */
    public function usage(): HasMany
    {
        return $this->hasMany(PlanUsageProxy::modelClass());
    }

    /**
     * Whether the tenant is open for business.
     */
    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE;
    }

    /**
     * The tenant's host, `{subdomain}.{platform domain}`.
     */
    public function host(): string
    {
        return $this->subdomain.'.'.config('tenancy.platform_domain');
    }

    /**
     * The tenant's root url, used for links built outside a request such as in mail and queued jobs.
     */
    public function url(): string
    {
        return config('tenancy.scheme').'://'.$this->host();
    }

    /**
     * Give a new tenant its uuid, and a database named after it so a subdomain rename never moves it.
     */
    protected static function booted(): void
    {
        static::creating(function (self $tenant) {
            $tenant->uuid ??= (string) Str::uuid();

            $tenant->database ??= config('tenancy.database.prefix').str_replace('-', '', $tenant->uuid);
        });
    }
}
