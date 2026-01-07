<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate pour les administrateurs (RH et Directeur)
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        // Gate pour les gardiens
        Gate::define('gardien', function (User $user) {
            return $user->isGardien();
        });

        // Gate pour les employés
        Gate::define('employe', function (User $user) {
            return $user->isEmploye();
        });
    }
}
