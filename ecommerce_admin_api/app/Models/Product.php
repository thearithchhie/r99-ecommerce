<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'sku',
        'name',
        'slug',
        'description',
        'base_price',
        'is_active',
        'is_featured',
        'category_id',
        'brand_id',
        'status_id',
        'order',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_price' => 'decimal:3',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'status_id' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the category that the product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get the brand that the product belongs to.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    
    /**
     * Get the product images.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    
    /**
     * Get the product variants.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
} 