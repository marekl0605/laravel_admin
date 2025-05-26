<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $user->hasRole('admin') || $user->companies()->where('companies.id', $company->id)->exists();
    }

    public function update(User $user, Company $company): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasRole('admin');
    }
}