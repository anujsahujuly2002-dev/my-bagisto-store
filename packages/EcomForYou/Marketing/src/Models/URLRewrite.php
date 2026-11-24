<?php

namespace EcomForYou\Marketing\Models;

use EcomForYou\Marketing\Contracts\URLRewrite as URLRewriteContract;
use EcomForYou\Marketing\Database\Factories\UrlRewriteFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class URLRewrite extends Model implements URLRewriteContract
{
    use HasFactory;

    /**
     * Define the table name for the model.
     *
     * @var string
     */
    protected $table = 'url_rewrites';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_type',
        'request_path',
        'target_path',
        'redirect_type',
        'locale',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return UrlRewriteFactory::new();
    }
}
