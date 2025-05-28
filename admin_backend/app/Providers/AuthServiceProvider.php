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
        \App\Models\Company::class => \App\Policies\CompanyPolicy::class,
        \App\Models\Person::class => \App\Policies\PersonPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\AuditLog::class => \App\Policies\AuditLogPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}