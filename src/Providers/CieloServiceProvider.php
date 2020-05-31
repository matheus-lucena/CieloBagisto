<?php

namespace Lucena\Cielo\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class CieloServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap services.
    *
    * @return void
    */
    public function boot(Router $router)
    {
        include dirname(__DIR__). '/Http/routes.php';
 
        $this->loadViewsFrom(dirname(__DIR__). '/Resources/views', 'cielo');

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php', 'core'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/paymentmethods.php', 'paymentmethods'
        );

        $this->loadTranslationsFrom(dirname(__DIR__). '/Resources/lang', 'cielo');

    }

}