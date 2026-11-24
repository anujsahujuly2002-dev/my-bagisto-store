<?php

namespace EcomForYou\Marketing\Models;

use EcomForYou\Marketing\Contracts\SearchSynonym as SearchSynonymContract;
use EcomForYou\Marketing\Database\Factories\SearchSynonymFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchSynonym extends Model implements SearchSynonymContract
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'terms',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return SearchSynonymFactory::new();
    }
}
