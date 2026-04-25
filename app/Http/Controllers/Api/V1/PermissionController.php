<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($module = trim((string) $request->input('module', ''))) {
            $query->where('module', $module);
        }

        $query->orderBy('module')->orderBy('action');

        return ApiResponse::success($query->get(), 'OK');
    }
}

