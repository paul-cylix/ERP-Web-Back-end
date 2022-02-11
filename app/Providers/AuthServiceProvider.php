<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Illuminate\Support\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
        Passport::routes(null, ['prefix' => 'api/oauth']);

        Passport::tokensExpireIn(Carbon::now()->addDays(1));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
        // login token expires
        Passport::personalAccessTokensExpireIn(Carbon::now()->addHours(12));
    }
}
