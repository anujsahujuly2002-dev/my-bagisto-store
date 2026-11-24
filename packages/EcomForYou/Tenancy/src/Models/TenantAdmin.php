<?php

namespace EcomForYou\Tenancy\Models;

use EcomForYou\Tenancy\Contracts\TenantAdmin as TenantAdminContract;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TenantAdmin extends Authenticatable implements TenantAdminContract
{
    /**
     * The landlord connection every platform admin lives on.
     *
     * @var string
     */
    protected $connection = 'landlord';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes hidden from serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'status' => 'boolean',
    ];
}
