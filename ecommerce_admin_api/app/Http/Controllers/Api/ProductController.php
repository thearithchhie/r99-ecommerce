<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products with pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get pagination parameters from the request
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        $search = $request->input('search', '');
        $categoryId = $request->input('category_id');
        $featured = $request->input('featured');
        
        // Query builder
        $query = Product::with(['category']);
        
        // Apply filters
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        if ($featured !== null) {
            $query->where('is_featured', $featured == 'true' || $featured == '1');
        }
        
        // Execute the query with pagination
        $products = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Return response
        return ApiResponse::ok([
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total()
            ]
        ], 'Products retrieved successfully', [], StatusCode::OK);
    }

    /**
     * Store a newly created product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }
        
        $validated = $validator->validated();
        
        // Generate slug from name
        $slug = Str::slug($validated['name']);
        
        // Check if the slug already exists, append a unique identifier if it does
        $count = Product::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Generate a unique SKU
        $sku = strtoupper(substr(str_replace(' ', '', $validated['name']), 0, 3)) . '-' . uniqid();
        
        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'uuid' => (string) Str::uuid(),
            'sku' => $sku,
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
            'status_id' => 1,
            'created_by' => $request->user()->id,
        ]);
        
        // Load the category relation
        $product->load('category');
        
        return ApiResponse::created($product, 'Product created successfully', [], StatusCode::OK);
    }

    /**
     * Display the specified product.
     *
     * @param  string  $id_or_slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id_or_slug)
    {
        // Find product by ID or slug
        $product = is_numeric($id_or_slug) 
            ? Product::with(['category'])->find($id_or_slug)
            : Product::with(['category'])->where('slug', $id_or_slug)->first();
        
        if (!$product) {
            return ApiResponse::notFound('Product not found', null, StatusCode::STATUS_NOT_FOUND_404);
        }
        
        return ApiResponse::ok($product, 'Product retrieved successfully', [], StatusCode::OK);
    }

    /**
     * Update the specified product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return ApiResponse::notFound('Product not found', null, StatusCode::STATUS_NOT_FOUND_404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'description' => 'nullable|string',
            'base_price' => 'sometimes|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }
        
        $validated = $validator->validated();
        
        // If name is being updated, update slug as well
        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            $slug = Str::slug($validated['name']);
            
            // Check if the new slug exists for other products
            $slugExists = Product::where('slug', $slug)->where('id', '!=', $id)->exists();
            
            if ($slugExists) {
                $slug = $slug . '-' . time();
            }
            
            $validated['slug'] = $slug;
        }
        
        // Update the product
        $product->fill($validated);
        $product->updated_by = $request->user()->id;
        $product->save();
        
        // Load category relation
        $product->load('category');
        
        return ApiResponse::ok($product, 'Product updated successfully', [], StatusCode::OK);
    }

    /**
     * Remove the specified product (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return ApiResponse::notFound('Product not found', null, StatusCode::STATUS_NOT_FOUND_404);
        }
        
        // Record who deleted this product
        $product->deleted_by = $request->user()->id;
        $product->save();
        
        // Soft delete the product
        $product->delete();
        
        return ApiResponse::ok(null, 'Product deleted successfully', [], StatusCode::OK);
    }
    
    /**
     * List all categories for the product form.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $categories = Category::all();
        
        return ApiResponse::ok([
            'categories' => $categories
        ], 'Categories retrieved successfully', [], StatusCode::OK);
    }
} 