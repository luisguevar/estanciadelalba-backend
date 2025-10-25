<?php

namespace App\Providers;

use App\Models\Habitacion;
use App\Observers\HabitacionObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);
        Habitacion::observe(HabitacionObserver::class);
    }
}
