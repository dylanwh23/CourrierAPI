<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Compra;   
use App\Observers\CompraObserver; 

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
        //
        Compra::observe(CompraObserver::class);

    }
}
