<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['first_name', 'last_name', 'email', 'phone'];

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}