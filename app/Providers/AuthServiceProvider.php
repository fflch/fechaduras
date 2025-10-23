<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Admin;

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

        // Usuário logado
        Gate::define('logado', function ($user) {
            if($user) return True;
            return False;
        });

        // Admin da fechadura especifica 
        Gate::define('adminFechadura', function ($user, $fechadura) {

            if (in_array($user->codpes, config('senhaunica.admins'))) return true;

            $admin = Admin::where('codpes', $user->codpes)->where('fechadura_id', $fechadura->id)->get();
            if($admin->isNotEmpty()) return True;
            return False;
        });
    }
}
