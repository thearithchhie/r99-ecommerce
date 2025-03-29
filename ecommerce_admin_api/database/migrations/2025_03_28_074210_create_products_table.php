<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Color
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->length(100);
            $table->string('code')->length(50)->unique();
            $table->string('hex_code')->length(20)->nullable();

            // AuditLog
            $table->integer("status_id")->default(1); 
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });

        // Size
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
        });

        // Brand
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->length(100);
            $table->string('slug')->unique()->length(40);
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string("web_url")->nullable();
            $table->boolean('is_featured')->default(false);

            // AuditLog
             $table->integer("status_id")->default(1); 
             $table->integer("order")->default(1);
             $table->timestamp("created_at")->useCurrent();
             $table->integer("created_by");
             $table->timestamp("updated_at")->nullable();
             $table->integer("updated_by")->nullable();
             $table->integer("deleted_by")->nullable();
             $table->softDeletes();
        });

        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->length(100);
            $table->string('slug')->unique()->length(40);
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');

            // AuditLog
            $table->integer("status_id")->default(1); 
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });

        // Product
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->length(100)->unique();
            $table->string('sku')->length(50)->unique();
            $table->string('name');
            $table->string('slug')->length(40)->unique();
            $table->foreignId('category_id')->constrained('categories', 'id')->onDelete('set null');
            $table->foreignId('brand_id')->constrained('brands', 'id')->onDelete('set null');
           
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 3)->default(0.000);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // AuditLog
            $table->integer("status_id")->default(1); 
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });

        // Product Images 
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('image');
            $table->boolean('is_primary')->default(false);

            // AuditLog
            $table->integer("status_id")->default(1); 
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });

        // Product Variants
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products', 'id')->onDelete('cascade');
            $table->foreignId('color_id')->constrained('colors', 'id')->onDelete('set null');
            $table->foreignId('size_id')->constrained('sizes', 'id')->onDelete('set null');

            $table->integer('stock_quantity')->default(0);
            $table->decimal('price_adjustment', 10, 3)->default(0.000);
            $table->string('sku_extension');
            

            // AuditLog
            $table->integer("status_id")->default(1); 
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });

        // Product Variant Images
        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants', 'id')->onDelete('cascade');
            $table->string('image')->nullable();

            // AuditLog
            $table->integer("status_id")->default(1); 
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });

        // Cards
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('session_id');

            // AuditLog
            $table->integer("status_id")->default(1); 
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });

        // Card Items
        Schema::create('card_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('cards', 'id')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants', 'id')->onDelete('cascade');
            $table->integer('quantity')->default(0);

            // AuditLog
            $table->integer("status_id")->default(1); 
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
            
        });

        // Wishlist
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            // AuditLog
            $table->integer("status_id")->default(1);
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });

        // Wishlist Items
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained('wishlists', 'id')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants', 'id')->onDelete('cascade');
            // AuditLog
            $table->integer("status_id")->default(1);
            $table->integer("order")->default(1);
            $table->timestamp("created_at")->useCurrent();
            $table->integer("created_by");
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
            $table->integer("deleted_by")->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_variant_images');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('card_items');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('wishlist_items');
    }
};
