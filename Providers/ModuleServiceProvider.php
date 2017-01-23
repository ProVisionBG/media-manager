<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Providers;

use Caffeinated\Modules\Support\ServiceProvider;
use ProVision\MediaManager\Administration;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/Lang', 'media-manager');

        $this->publishes([
            __DIR__ . '/../Resources/Lang' => resource_path('lang/vendor/provision/media-manager'),
        ], 'lang');

        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'media-manager');

        $this->publishes([
            __DIR__ . '/../Public' => public_path('vendor/provision/media-manager'),
        ], 'public');

        \ProVision\Administration\Administration::bootModule('media-manager', Administration::class);

        \Form::component('mediaManager', 'media-manager::components.button', [
            'model',
        ]);
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        //$this->app->register(RouteServiceProvider::class);
    }
}
