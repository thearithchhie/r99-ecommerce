<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Check the health of the API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function check()
    {
        $status = 'OK';
        $dbStatus = 'OK';
        
        // Try to connect to the database
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'ERROR: ' . $e->getMessage();
            $status = 'PARTIAL';
        }
        
        return response()->json([
            'status' => $status,
            'message' => 'API is running',
            'timestamp' => now()->toIso8601String(),
            'components' => [
                'database' => $dbStatus
            ]
        ]);
    }
} 