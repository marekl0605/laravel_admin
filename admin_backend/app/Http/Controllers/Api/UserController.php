<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $users = $request->user()->hasRole('admin')
            ? User::all()
            : User::whereHas('companies', fn($q) => $q->whereIn('companies.id', $request->user()->companies->pluck('id')))->get();
        return response()->json(['users' => $users]);
    }

    public function show(User $user): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $user);
        return response()->json(['user' => $user->load(['roles', 'permissions', 'companies', 'people'])]);
    }

    public function update(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $user);
        $validated = $request->validate([
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'role_ids' => 'sometimes|array',
            'role_ids.*' => 'exists:roles,id',
            'permission_ids' => 'sometimes|array',
            'permission_ids.*' => 'exists:permissions,id',
            'company_ids' => 'sometimes|array',
            'company_ids.*' => 'exists:companies,id',
            'person_ids' => 'sometimes|array',
            'person_ids.*' => 'exists:people,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);
        if (isset($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }
        if (isset($validated['permission_ids'])) {
            $user->permissions()->sync($validated['permission_ids']);
        }
        if (isset($validated['company_ids'])) {
            $user->companies()->sync($validated['company_ids']);
        }
        if (isset($validated['person_ids'])) {
            $user->people()->sync($validated['person_ids']);
        }

        return response()->json(['message' => 'User updated', 'user' => $user]);
    }

    public function destroy(User $user): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}