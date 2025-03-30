<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        // Apply search filter
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply featured filter
        if ($request->has('featured')) {
            $featured = filter_var($request->input('featured'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_featured', $featured);
        }

        // Apply status filter
        if ($request->has('status_id')) {
            $query->where('status_id', $request->input('status_id'));
        }

        // Apply sorting
        $sortField = $request->input('sort_by', 'order');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Get pagination parameters from the request
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        
        // Apply limits to prevent excessive requests
        $perPage = min(max($perPage, 5), 100); // Between 5 and 100 items per page
        
        // Get paginated results
        $brands = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Create response with just the brand items
        $response = ApiResponse::ok([
            'brands' => $brands->items()
        ], 'Brands retrieved successfully', [], 200);
        
        // Add pagination metadata
        return $response->withPagination($brands);
    }

    /**
     * Store a newly created brand in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string',
            'web_url' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean',
            'status_id' => 'nullable|integer',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError(
                'Validation Error',
                $validator->errors()->toArray(),
                StatusCode::STATUS_VALIDATION_ERROR_422
            );
        }

        $data = $validator->validated();
        
        // Generate slug
        $data['slug'] = Str::slug($data['name']);
        
        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $filename = 'brand_logo_' . time() . '.' . $logo->getClientOriginalExtension();
            $path = $logo->storeAs('public/brands/logos', $filename);
            $data['logo'] = Storage::url($path);
        }
        
        // Add created_by user
        $data['created_by'] = Auth::id();
        
        // Create the brand
        $brand = Brand::create($data);
        
        return ApiResponse::success(
            'Brand created successfully',
            $brand,
            201
        );
    }

    /**
     * Display the specified brand.
     *
     * @param  string  $id_or_slug
     * @return \Illuminate\Http\Response
     */
    public function show($id_or_slug)
    {
        $brand = is_numeric($id_or_slug)
            ? Brand::find($id_or_slug)
            : Brand::where('slug', $id_or_slug)->first();
            
        if (!$brand) {
            return ApiResponse::error(
                'Brand not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        return ApiResponse::success(
            'Brand details retrieved successfully',
            $brand,
            200
        );
    }

    /**
     * Update the specified brand in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        
        if (!$brand) {
            return ApiResponse::error(
                'Brand not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:brands,name,' . $id,
            'description' => 'nullable|string',
            'web_url' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean',
            'status_id' => 'nullable|integer',
            'order' => 'nullable|integer',
        ]);
        
        if ($validator->fails()) {
            return ApiResponse::validationError(
                'Validation Error',
                $validator->errors()->toArray(),
                StatusCode::STATUS_VALIDATION_ERROR_422
            );
        }
        
        $data = $validator->validated();
        
        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $brand->name) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            // Delete the old logo if it exists
            if ($brand->logo) {
                $oldPath = str_replace(Storage::url(''), '', $brand->logo);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }
            
            $logo = $request->file('logo');
            $filename = 'brand_logo_' . time() . '.' . $logo->getClientOriginalExtension();
            $path = $logo->storeAs('public/brands/logos', $filename);
            $data['logo'] = Storage::url($path);
        }
        
        // Add updated_by user
        $data['updated_by'] = Auth::id();
        
        $brand->update($data);
        
        return ApiResponse::success(
            'Brand updated successfully',
            $brand,
            200
        );
    }

    /**
     * Remove the specified brand from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $brand = Brand::find($id);
        
        if (!$brand) {
            return ApiResponse::error(
                'Brand not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        // Check if brand has products
        $productsCount = $brand->products()->count();
        if ($productsCount > 0) {
            return ApiResponse::error(
                'Cannot delete brand that has products. Please delete the products first or reassign them.',
                ['products_count' => $productsCount],
                StatusCode::STATUS_BAD_REQUEST_400
            );
        }
        
        // Record deleted_by user ID
        $brand->update(['updated_by' => Auth::id()]);
        
        $brand->delete();
        
        return ApiResponse::success(
            'Brand deleted successfully',
            null,
            200
        );
    }
} 