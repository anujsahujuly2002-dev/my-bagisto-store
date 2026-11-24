<?php

namespace EcomForYou\Tenancy\Models;

use EcomForYou\Tenancy\Contracts\PlanUsage as PlanUsageContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanUsage extends Model implements PlanUsageContract
{
    /**
     * The landlord connection every usage record lives on.
     *
     * @var string
     */
    protected $connection = 'landlord';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plan_usage';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'metric',
        'value',
        'period',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'integer',
    ];

    /**
     * The tenant the usage was measured in.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantProxy::modelClass());
    }
}
