<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'stock_quantity',
        'price_adjustment',
        'sku_extension',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stock_quantity' => 'integer',
        'price_adjustment' => 'decimal:2',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the color associated with this variant.
     */
    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Get the size associated with this variant.
     */
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Get the images for this variant.
     */
    public function images()
    {
        return $this->hasMany(ProductVariantImage::class);
    }
} 