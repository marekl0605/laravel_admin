<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

class PersonPolicy
{
    public function view(User $user, Person $person): bool
    {
        return $user->hasRole('admin') ||
               $person->companies()->whereIn('companies.id', $user->companies->pluck('id'))->exists();
    }

    public function update(User $user, Person $person): bool
    {
        return $user->hasRole('admin') || $user->hasPermission('manage-people');
    }

    public function delete(User $user, Person $person): bool
    {
        return $user->hasRole('admin') || $user->hasPermission('manage-people');
    }
}