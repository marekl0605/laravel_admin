<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $users = User::all();
        return response()->json(['users' => $users]);
    }

    public function show(User $user): \Illuminate\Http\JsonResponse
    {
        return response()->json(['user' => $user]);
    }

    public function update(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);
        return response()->json(['message' => 'User updated', 'user' => $user]);
    }

    public function destroy(User $user): \Illuminate\Http\JsonResponse
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}