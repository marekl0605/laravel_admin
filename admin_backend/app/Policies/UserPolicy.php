<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $authUser, User $user): bool
    {
        return $authUser->hasRole('admin') ||
               $user->companies()->whereIn('companies.id', $authUser->companies->pluck('id'))->exists();
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->hasRole('admin');
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasRole('admin');
    }
}