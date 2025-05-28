<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company_ids' => 'sometimes|array',
            'company_ids.*' => 'exists:companies,id',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'person_id' => 'sometimes|exists:people,id',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        if (isset($validated['person_id'])) {
            $user->people()->attach($validated['person_id']);
        } elseif (isset($validated['first_name'], $validated['last_name'])) {
            $person = Person::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
            ]);
            $user->people()->attach($person->id);
            if (isset($validated['company_ids'])) {
                $person->companies()->sync($validated['company_ids']);
            }
        }

        $user->roles()->attach(Role::where('name', 'user')->first());
        if (isset($validated['company_ids'])) {
            $user->companies()->sync($validated['company_ids']);
        }

        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user->load(['companies', 'people']),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out']);
    }
}