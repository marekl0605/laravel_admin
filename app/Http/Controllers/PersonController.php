<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PersonController extends Controller
{
    /**
     * Display a listing of people
     */
    public function index(Request $request)
    {
        $query = Person::with(['companies', 'users']);

        // Filter by company
        if ($request->has('company_id')) {
            $query->whereHas('companies', function ($q) use ($request) {
                $q->where('companies.id', $request->company_id);
            });
        }

        // Filter authenticated/non-authenticated people
        if ($request->has('authenticated')) {
            if ($request->authenticated === 'true') {
                $query->authenticated();
            } else {
                $query->nonAuthenticated();
            }
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $people = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $people
        ]);
    }

    /**
     * Store a newly created person
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:people',
            'company_ids' => 'array',
            'company_ids.*' => 'exists:companies,id',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $personData = $request->except(['company_ids', 'user_ids']);
        $person = Person::create($personData);

        // Attach companies
        if ($request->has('company_ids')) {
            $person->companies()->attach($request->company_ids);
        }

        // Attach users
        if ($request->has('user_ids')) {
            $person->users()->attach($request->user_ids);
        }

        $person->load(['companies', 'users']);

        return response()->json([
            'success' => true,
            'message' => 'Person created successfully',
            'data' => $person
        ], 201);
    }

    /**
     * Display the specified person
     */
    public function show(Person $person)
    {
        $person->load(['companies', 'users']);

        return response()->json([
            'success' => true,
            'data' => $person
        ]);
    }

    /**
     * Update the specified person
     */
    public function update(Request $request, Person $person)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:people,email,' . $person->id,
            'company_ids' => 'array',
            'company_ids.*' => 'exists:companies,id',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $personData = $request->except(['company_ids', 'user_ids']);
        $person->update($personData);

        // Sync companies
        if ($request->has('company_ids')) {
            $person->companies()->sync($request->company_ids);
        }

        // Sync users
        if ($request->has('user_ids')) {
            $person->users()->sync($request->user_ids);
        }

        $person->load(['companies', 'users']);

        return response()->json([
            'success' => true,
            'message' => 'Person updated successfully',
            'data' => $person
        ]);
    }

    /**
     * Remove the specified person
     */
    public function destroy(Person $person)
    {
        $person->delete();

        return response()->json([
            'success' => true,
            'message' => 'Person deleted successfully'
        ]);
    }

    /**
     * Get person's companies
     */
    public function companies(Person $person)
    {
        $companies = $person->companies()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Get person's users
     */
    public function users(Person $person)
    {
        $users = $person->users()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Link person to user account
     */
    public function linkUser(Request $request, Person $person)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if already linked
        if ($person->users()->where('user_id', $request->user_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Person is already linked to this user'
            ], 400);
        }

        $person->users()->attach($request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Person linked to user successfully'
        ]);
    }

    /**
     * Unlink person from user account
     */
    public function unlinkUser(Request $request, Person $person)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $person->users()->detach($request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Person unlinked from user successfully'
        ]);
    }
}