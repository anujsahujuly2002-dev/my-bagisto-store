<?php

namespace EcomForYou\Core\Models;

use EcomForYou\Core\Contracts\CoreConfig as CoreConfigContract;
use EcomForYou\Core\Database\Factories\CoreConfigFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreConfig extends Model implements CoreConfigContract
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'core_config';

    /**
     * Fillable for mass assignment
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'value',
        'channel_code',
        'locale_code',
    ];

    /**
     * Hidden properties
     *
     * @var array
     */
    protected $hidden = ['token'];

    /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): Factory
    {
        return CoreConfigFactory::new();
    }
}
