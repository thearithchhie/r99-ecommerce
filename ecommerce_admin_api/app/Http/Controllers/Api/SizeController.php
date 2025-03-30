<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SizeController extends Controller
{
    /**
     * Display a listing of the sizes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Size::query();

        // Apply search filter
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('code', 'like', "%{$searchTerm}%");
            });
        }

        // Apply sorting
        $sortField = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Get paginated results
        $perPage = $request->input('per_page', 15);
        $sizes = $query->paginate($perPage);

        return ApiResponse::ok(
            $sizes,
            'Sizes retrieved successfully',
            [],
            StatusCode::OK
        );
    }

    /**
     * Store a newly created size in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:sizes,name',
            'code' => 'required|string|max:20|unique:sizes,code',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError(
                'Validation Error',
                $validator->errors()->toArray(),
                StatusCode::STATUS_VALIDATION_ERROR_422
            );
        }

        $data = $validator->validated();
        
        // Add created_by user
        $data['created_by'] = Auth::id();
        
        // Create the size
        $size = Size::create($data);
        
        return ApiResponse::created(
            $size,
            'Size created successfully',
            [],
            StatusCode::OK
        );
    }

    /**
     * Display the specified size.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $size = Size::find($id);
            
        if (!$size) {
            return ApiResponse::notFound(
                'Size not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        return ApiResponse::ok(
            $size,
            'Size details retrieved successfully',
            [],
            StatusCode::OK
        );
    }

    /**
     * Update the specified size in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $size = Size::find($id);
        
        if (!$size) {
            return ApiResponse::notFound(
                'Size not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:50|unique:sizes,name,' . $id,
            'code' => 'string|max:20|unique:sizes,code,' . $id,
        ]);
        
        if ($validator->fails()) {
            return ApiResponse::validationError(
                'Validation Error',
                $validator->errors()->toArray(),
                StatusCode::STATUS_VALIDATION_ERROR_422
            );
        }
        
        $data = $validator->validated();
        
        // Add updated_by user
        $data['updated_by'] = Auth::id();
        
        $size->update($data);
        
        return ApiResponse::ok(
            $size,
            'Size updated successfully',
            [],
            StatusCode::OK
        );
    }

    /**
     * Remove the specified size from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $size = Size::find($id);
        
        if (!$size) {
            return ApiResponse::notFound(
                'Size not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        // Check if size has product variants
        $variantsCount = $size->productVariants()->count();
        if ($variantsCount > 0) {
            return ApiResponse::badRequest(
                'Cannot delete size that is used by product variants',
                ['variants_count' => $variantsCount],
                StatusCode::STATUS_BAD_REQUEST_400
            );
        }
        
        // Record deleted_by user ID
        $size->update(['updated_by' => Auth::id()]);
        
        $size->delete();
        
        return ApiResponse::ok(
            null,
            'Size deleted successfully',
            [],
            StatusCode::OK
        );
    }
} 