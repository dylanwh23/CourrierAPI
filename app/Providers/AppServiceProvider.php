<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use App\Models\Mensajes;
use App\Observers\MensajesObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
       Broadcast::routes(['middleware' => ['web', 'auth']]);
    }

    public function register()
    {
        //
    }
}