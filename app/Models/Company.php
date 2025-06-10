<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{


    protected $fillable = [
        'name',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function people()
    {
        return $this->belongsToMany(Person::class);
    }

    /**
     * Get all authenticated users belonging to this company
     */
    public function authenticatedUsers()
    {
        return $this->users()->where('status', 'active');
    }

    /**
     * Get all people (authenticated and non-authenticated) belonging to this company
     */
    public function allPeople()
    {
        return $this->people();
    }

    /**
     * Get people who are also authenticated users
     */
    public function authenticatedPeople()
    {
        return $this->people()->whereHas('users');
    }

    /**
     * Get people who are not authenticated users
     */
    public function nonAuthenticatedPeople()
    {
        return $this->people()->whereDoesntHave('users');
    }
}
