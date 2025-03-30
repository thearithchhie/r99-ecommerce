<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ColorController extends Controller
{
    /**
     * Display a listing of the colors.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Color::query();

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
        $colors = $query->paginate($perPage);

        return ApiResponse::ok(
            $colors,
            'Colors retrieved successfully',
            [],
            StatusCode::OK
        );
    }

    /**
     * Store a newly created color in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:colors,name',
            'code' => 'nullable|string|max:20',
            'hex_code' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
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
        
        // Create the color
        $color = Color::create($data);
        
        return ApiResponse::created(
            $color,
            'Color created successfully',
            [],
            StatusCode::OK
        );
    }

    /**
     * Display the specified color.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $color = Color::find($id);
            
        if (!$color) {
            return ApiResponse::notFound(
                'Color not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        return ApiResponse::ok(
            $color,
            'Color details retrieved successfully',
            [],
            StatusCode::OK
        );
    }

    /**
     * Update the specified color in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $color = Color::find($id);
        
        if (!$color) {
            return ApiResponse::notFound(
                'Color not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:50|unique:colors,name,' . $id,
            'code' => 'nullable|string|max:20',
            'hex_code' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
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
        
        $color->update($data);
        
        return ApiResponse::ok(
            $color,
            'Color updated successfully',
            [],
            StatusCode::OK
        );
    }

    /**
     * Remove the specified color from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $color = Color::find($id);
        
        if (!$color) {
            return ApiResponse::notFound(
                'Color not found',
                null,
                StatusCode::STATUS_NOT_FOUND_404
            );
        }
        
        // Check if color has product variants
        $variantsCount = $color->productVariants()->count();
        if ($variantsCount > 0) {
            return ApiResponse::badRequest(
                'Cannot delete color that is used by product variants',
                ['variants_count' => $variantsCount],
                StatusCode::STATUS_BAD_REQUEST_400
            );
        }
        
        // Record deleted_by user ID
        $color->update(['updated_by' => Auth::id()]);
        
        $color->delete();
        
        return ApiResponse::ok(
            null,
            'Color deleted successfully',
            [],
            StatusCode::OK
        );
    }
} 