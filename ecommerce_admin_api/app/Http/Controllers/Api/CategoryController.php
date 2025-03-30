<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with pagination.
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
        $parentId = $request->input('parent_id');
        $featured = $request->input('featured');
        
        // Query builder
        $query = Category::with(['parent']);
        
        // Apply filters
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($parentId !== null) {
            $query->where('parent_id', $parentId ?: null);
        }
        
        if ($featured !== null) {
            $query->where('is_featured', $featured == 'true' || $featured == '1');
        }
        
        // Execute the query with pagination
        $categories = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Return response
        return ApiResponse::ok([
            'categories' => $categories->items(),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total()
            ]
        ], 'Categories retrieved successfully', [], StatusCode::OK);
    }

    /**
     * Store a newly created category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }
        
        $validated = $validator->validated();
        
        // Generate slug from name
        $slug = Str::slug($validated['name']);
        
        // Check if the slug already exists
        $count = Category::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . time();
        }
        
        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_featured' => $validated['is_featured'] ?? false,
            'status_id' => 1,
            'created_by' => $request->user()->id,
        ]);
        
        // Load the parent relation
        $category->load('parent');
        
        return ApiResponse::created($category, 'Category created successfully', [], StatusCode::OK);
    }

    /**
     * Display the specified category.
     *
     * @param  string  $id_or_slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id_or_slug)
    {
        // Find category by ID or slug
        $category = is_numeric($id_or_slug) 
            ? Category::with(['parent', 'children'])->find($id_or_slug)
            : Category::with(['parent', 'children'])->where('slug', $id_or_slug)->first();
        
        if (!$category) {
            return ApiResponse::notFound('Category not found', null, StatusCode::STATUS_NOT_FOUND_404);
        }
        
        return ApiResponse::ok($category, 'Category retrieved successfully', [], StatusCode::OK);
    }

    /**
     * Update the specified category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        
        if (!$category) {
            return ApiResponse::notFound('Category not found', null, StatusCode::STATUS_NOT_FOUND_404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }
        
        $validated = $validator->validated();
        
        // Prevent category from being its own parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $id) {
            return ApiResponse::badRequest('A category cannot be its own parent', null, StatusCode::STATUS_BAD_REQUEST_400);
        }
        
        // If name is being updated, update slug as well
        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            $slug = Str::slug($validated['name']);
            
            // Check if the new slug exists for other categories
            $slugExists = Category::where('slug', $slug)->where('id', '!=', $id)->exists();
            
            if ($slugExists) {
                $slug = $slug . '-' . time();
            }
            
            $validated['slug'] = $slug;
        }
        
        // Update the category
        $category->fill($validated);
        $category->updated_by = $request->user()->id;
        $category->save();
        
        // Load relations
        $category->load(['parent', 'children']);
        
        return ApiResponse::ok($category, 'Category updated successfully', [], StatusCode::OK);
    }

    /**
     * Remove the specified category (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $category = Category::find($id);
        
        if (!$category) {
            return ApiResponse::notFound('Category not found', null, StatusCode::STATUS_NOT_FOUND_404);
        }
        
        // Check if category has children
        $hasChildren = Category::where('parent_id', $id)->exists();
        if ($hasChildren) {
            return ApiResponse::badRequest('Cannot delete category with subcategories', null, StatusCode::STATUS_BAD_REQUEST_400);
        }
        
        // Record who deleted this category
        $category->deleted_by = $request->user()->id;
        $category->save();
        
        // Soft delete the category
        $category->delete();
        
        return ApiResponse::ok(null, 'Category deleted successfully', [], StatusCode::OK);
    }
    
    /**
     * Get a hierarchical list of categories for dropdowns.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hierarchy()
    {
        // Get root categories (those without parents)
        $rootCategories = Category::whereNull('parent_id')->with('children')->get();
        
        // Format categories into a hierarchical structure
        $hierarchical = $this->formatCategoryHierarchy($rootCategories);
        
        return ApiResponse::ok([
            'categories' => $hierarchical
        ], 'Category hierarchy retrieved successfully', [], StatusCode::OK);
    }
    
    /**
     * Helper method to format category hierarchy.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $categories
     * @param  int  $level
     * @return array
     */
    private function formatCategoryHierarchy($categories, $level = 0)
    {
        $result = [];
        
        foreach ($categories as $category) {
            $categoryData = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'level' => $level,
                'label' => str_repeat('— ', $level) . $category->name,
            ];
            
            // Add category to result
            $result[] = $categoryData;
            
            // Process children if any
            if ($category->children && $category->children->count() > 0) {
                $childrenData = $this->formatCategoryHierarchy($category->children, $level + 1);
                $result = array_merge($result, $childrenData);
            }
        }
        
        return $result;
    }
} 