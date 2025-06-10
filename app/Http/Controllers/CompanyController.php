<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies
     */
    public function index(Request $request)
    {
        $query = Company::with(['users', 'people']);

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $companies = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
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

        $companyData = $request->except(['user_ids', 'person_ids']);
        $company = Company::create($companyData);

        // Attach users
        if ($request->has('user_ids')) {
            $company->users()->attach($request->user_ids);
        }

        // Attach people
        if ($request->has('person_ids')) {
            $company->people()->attach($request->person_ids);
        }

        $company->load(['users', 'people']);

        return response()->json([
            'success' => true,
            'message' => 'Company created successfully',
            'data' => $company
        ], 201);
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        $company->load(['users', 'people']);

        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
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

        $companyData = $request->except(['user_ids', 'person_ids']);
        $company->update($companyData);

        // Sync users
        if ($request->has('user_ids')) {
            $company->users()->sync($request->user_ids);
        }

        // Sync people
        if ($request->has('person_ids')) {
            $company->people()->sync($request->person_ids);
        }

        $company->load(['users', 'people']);

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => $company
        ]);
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully'
        ]);
    }

    /**
     * Get company's users
     */
    public function users(Company $company, Request $request)
    {
        $query = $company->users();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get company's people
     */
    public function people(Company $company, Request $request)
    {
        $query = $company->people();

        // Filter authenticated/non-authenticated people
        if ($request->has('authenticated')) {
            if ($request->authenticated === 'true') {
                $query->whereHas('users');
            } else {
                $query->whereDoesntHave('users');
            }
        }

        $people = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $people
        ]);
    }

    /**
     * Get only authenticated users for the company
     */
    public function authenticatedUsers(Company $company)
    {
        $users = $company->authenticatedUsers()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get authenticated people for the company
     */
    public function authenticatedPeople(Company $company)
    {
        $people = $company->authenticatedPeople()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $people
        ]);
    }

    /**
     * Get non-authenticated people for the company
     */
    public function nonAuthenticatedPeople(Company $company)
    {
        $people = $company->nonAuthenticatedPeople()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $people
        ]);
    }
}