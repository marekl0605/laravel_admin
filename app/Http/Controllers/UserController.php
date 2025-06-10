<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['companies', 'people']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('company_id')) {
            $query->whereHas('companies', function ($q) use ($request) {
                $q->where('companies.id', $request->company_id);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'username' => 'nullable|string|unique:users|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'student_id' => 'nullable|string|unique:users',
            'employee_id' => 'nullable|string|unique:users',
            'status' => 'in:active,inactive,suspended',
            'company_ids' => 'array',
            'company_ids.*' => 'exists:companies,id',
            'person_ids' => 'array',
            'person_ids.*' => 'exists:people,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->except(['company_ids', 'person_ids']);
        
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        $user = User::create($userData);

        // Attach companies
        if ($request->has('company_ids')) {
            $user->companies()->attach($request->company_ids);
        }

        // Attach people
        if ($request->has('person_ids')) {
            $user->people()->attach($request->person_ids);
        }

        $user->load(['companies', 'people']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['companies', 'people']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'student_id' => 'nullable|string|unique:users,student_id,' . $user->id,
            'employee_id' => 'nullable|string|unique:users,employee_id,' . $user->id,
            'status' => 'in:active,inactive,suspended',
            'company_ids' => 'array',
            'company_ids.*' => 'exists:companies,id',
            'person_ids' => 'array',
            'person_ids.*' => 'exists:people,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->except(['company_ids', 'person_ids']);
        
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        $user->update($userData);

        // Sync companies
        if ($request->has('company_ids')) {
            $user->companies()->sync($request->company_ids);
        }

        // Sync people
        if ($request->has('person_ids')) {
            $user->people()->sync($request->person_ids);
        }

        $user->load(['companies', 'people']);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get user's companies
     */
    public function companies(User $user)
    {
        $companies = $user->companies()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Get user's people
     */
    public function people(User $user)
    {
        $people = $user->people()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $people
        ]);
    }
}