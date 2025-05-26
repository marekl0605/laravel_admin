<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function people()
    {
        return $this->belongsToMany(Person::class);
    }
}