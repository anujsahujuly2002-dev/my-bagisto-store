<?php

namespace EcomForYou\Marketing\Models;

use EcomForYou\Marketing\Contracts\Event as EventContract;
use EcomForYou\Marketing\Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model implements EventContract
{
    use HasFactory;

    /**
     * Define the models table name
     *
     * @var string
     */
    protected $table = 'marketing_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'date',
    ];

    /**
     * The campaigns that send with this event.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(CampaignProxy::modelClass(), 'marketing_event_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return EventFactory::new();
    }
}
