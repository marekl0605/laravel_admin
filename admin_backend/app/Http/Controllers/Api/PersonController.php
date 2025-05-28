<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Person::with('companies');

        if (!$request->user()->hasRole('admin') && !$request->user()->hasPermission('manage-people')) {
            $query->whereHas('companies', fn($q) => $q->whereIn('companies.id', $request->user()->companies->pluck('id')));
        }

        $people = $query->paginate(10);
        return response()->json(['people' => $people]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:people',
            'phone' => 'nullable|string|max:20',
            'company_ids' => 'sometimes|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        $person = Person::create($validated);
        if (isset($validated['company_ids'])) {
            $person->companies()->sync($validated['company_ids']);
        }
        return response()->json(['message' => 'Person created', 'person' => $person], 201);
    }

    public function show(Person $person): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $person);
        return response()->json(['person' => $person->load('companies')]);
    }

    public function update(Request $request, Person $person): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $person);
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:people,email,' . $person->id,
            'phone' => 'nullable|string|max:20',
            'company_ids' => 'sometimes|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        $person->update($validated);
        if (isset($validated['company_ids'])) {
            $person->companies()->sync($validated['company_ids']);
        }
        return response()->json(['message' => 'Person updated', 'person' => $person]);
    }

    public function destroy(Person $person): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $person);
        $person->delete();
        return response()->json(['message' => 'Person deleted']);
    }
}