<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Company::query();
        if (!$request->user()->hasRole('admin')) {
            $query->whereHas('users', fn($q) => $q->where('users.id', $request->user()->id));
        }
        $companies = $query->paginate(10);
        return response()->json(['companies' => $companies]);
    }
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $company = Company::create($validated);
        return response()->json(['message' => 'Company created', 'company' => $company], 201);
    }

    public function show(Company $company): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $company);
        return response()->json(['company' => $company]);
    }

    public function update(Request $request, Company $company): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $company);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:companies,name,' . $company->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $company->update($validated);
        return response()->json(['message' => 'Company updated', 'company' => $company]);
    }

    public function destroy(Company $company): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $company);
        $company->delete();
        return response()->json(['message' => 'Company deleted']);
    }
}