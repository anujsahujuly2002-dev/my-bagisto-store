<?php

namespace EcomForYou\Marketing\Models;

use EcomForYou\Marketing\Contracts\Template as TemplateContract;
use EcomForYou\Marketing\Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model implements TemplateContract
{
    use HasFactory;

    /**
     * Define the table name for the Model
     *
     * @var string
     */
    protected $table = 'marketing_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'content',
    ];

    /**
     * The campaigns that send with this template.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(CampaignProxy::modelClass(), 'marketing_template_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TemplateFactory::new();
    }
}
