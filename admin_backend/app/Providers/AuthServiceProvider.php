<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Person;
use App\Models\Post;
use App\Models\User;
use App\Policies\CompanyPolicy;
use App\Policies\PersonPolicy;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Post::class => PostPolicy::class,
        Company::class => CompanyPolicy::class,
        Person::class => PersonPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}