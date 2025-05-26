<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $permissions = Permission::all();
        return response()->json(['permissions' => $permissions]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
            'description' => 'nullable|string',
        ]);

        $permission = Permission::create($validated);
        return response()->json(['message' => 'Permission created', 'permission' => $permission], 201);
    }

    public function show(Permission $permission): \Illuminate\Http\JsonResponse
    {
        return response()->json(['permission' => $permission]);
    }

    public function update(Request $request, Permission $permission): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:permissions,name,' . $permission->id,
            'description' => 'nullable|string',
        ]);

        $permission->update($validated);
        return response()->json(['message' => 'Permission updated', 'permission' => $permission]);
    }

    public function destroy(Permission $permission): \Illuminate\Http\JsonResponse
    {
        $permission->delete();
        return response()->json(['message' => 'Permission deleted']);
    }
}