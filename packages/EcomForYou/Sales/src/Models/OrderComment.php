<?php

namespace EcomForYou\Sales\Models;

use EcomForYou\Sales\Contracts\OrderComment as OrderCommentContract;
use Illuminate\Database\Eloquent\Model;

class OrderComment extends Model implements OrderCommentContract
{
    protected $fillable = [
        'comment',
        'customer_notified',
        'order_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'customer_notified' => 'boolean',
    ];

    /**
     * Get the order record associated with the order comment.
     */
    public function order()
    {
        return $this->belongsTo(OrderProxy::modelClass());
    }
}
