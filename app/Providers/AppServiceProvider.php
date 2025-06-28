<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use App\Models\Compra;   
use App\Observers\CompraObserver; 

use Illuminate\Auth\Notifications\VerifyEmail;
use App\Notifications\MyCustomVerifyEmail;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
       Broadcast::routes(['middleware' => ['web', 'auth']]);
        Compra::observe(CompraObserver::class);
         VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MyCustomVerifyEmail())->toMail($notifiable);
        });

    }

    public function register()
    {
        //

    }
}