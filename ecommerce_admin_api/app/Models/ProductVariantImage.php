<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariantImage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_variant_id',
        'image',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the product variant that owns this image.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
} 