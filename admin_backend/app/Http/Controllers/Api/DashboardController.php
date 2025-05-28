<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        // Base query for company-restricted data
        $query = fn($q) => $isAdmin ? $q : $q->whereHas('companies', fn($c) => $c->whereIn('companies.id', $user->companies->pluck('id')));

        $stats = [
            'total_users' => $query(User::query())->count(),
            'total_companies' => $isAdmin ? Company::count() : $user->companies()->count(),
            'total_people' => $query(Person::query())->count(),
            'recent_users' => $query(User::query())
                ->latest()
                ->take(5)
                ->get(['id', 'username', 'email']),
            'recent_people' => $query(Person::query())
                ->latest()
                ->take(5)
                ->get(['id', 'first_name', 'last_name', 'email']),
        ];

        return response()->json(['stats' => $stats]);
    }
}