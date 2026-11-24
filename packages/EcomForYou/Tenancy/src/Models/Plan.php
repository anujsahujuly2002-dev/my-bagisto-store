<?php

namespace EcomForYou\Tenancy\Models;

use EcomForYou\Tenancy\Contracts\Plan as PlanContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model implements PlanContract
{
    /**
     * The landlord connection every plan lives on.
     *
     * @var string
     */
    protected $connection = 'landlord';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'currency',
        'billing_period',
        'trial_days',
        'limits',
        'features',
        'is_public',
        'sort_order',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:4',
        'trial_days' => 'integer',
        'limits' => 'array',
        'features' => 'array',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The tenants on this package.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(TenantProxy::modelClass());
    }
}
