<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AuditLogController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        // Ensure only admins can access
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::with(['user' => fn($q) => $q->select('id', 'username', 'email')])
                       ->latest()
                       ->paginate(20);

        return response()->json(['logs' => $logs]);
    }
}