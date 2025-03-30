<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Size extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the product variants that use this size.
     */
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
} 