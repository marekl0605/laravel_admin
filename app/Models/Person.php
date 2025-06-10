<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Check if person is authenticated (has user account)
     */
    public function getIsAuthenticatedAttribute()
    {
        return $this->users()->exists();
    }

    /**
     * Get the primary user account if exists
     */
    public function getPrimaryUserAttribute()
    {
        return $this->users()->first();
    }

    /**
     * Scope for authenticated people (who have user accounts)
     */
    public function scopeAuthenticated($query)
    {
        return $query->whereHas('users');
    }

    /**
     * Scope for non-authenticated people (who don't have user accounts)
     */
    public function scopeNonAuthenticated($query)
    {
        return $query->whereDoesntHave('users');
    }
}
