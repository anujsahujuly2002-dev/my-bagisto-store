<?php

namespace Webkul\Theme\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Core\Models\ChannelProxy;
use Webkul\Theme\Contracts\ThemeSchedule as ThemeScheduleContract;

class ThemeSchedule extends Model implements ThemeScheduleContract
{
    /**
     * A schedule whose start has not come yet.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * A schedule whose theme is live on its channel.
     */
    public const STATUS_RUNNING = 'running';

    /**
     * A schedule that has ended.
     */
    public const STATUS_FINISHED = 'finished';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'channel_theme_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'channel_id',
        'theme_code',
        'previous_theme',
        'starts_at',
        'ends_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the channel the schedule switches.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChannelProxy::modelClass());
    }
}
