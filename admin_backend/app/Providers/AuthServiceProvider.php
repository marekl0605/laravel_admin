<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Person;
use App\Models\User;
use App\Policies\CompanyPolicy;
use App\Policies\PersonPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Company::class => CompanyPolicy::class,
        Person::class => PersonPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}