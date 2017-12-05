<?php
/**
 * Copyright (c) 2017. ProVision Media Group Ltd. <http://provision.bg>
 * Venelin Iliev <http://veneliniliev.com>
 */

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Providers;

use Caffeinated\Modules\Support\ServiceProvider;
use ProVision\MediaManager\Administration;
use ProVision\MediaManager\Console\Commands\MediaResize;

class ModuleServiceProvider extends ServiceProvider {
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot() {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/Lang', 'media-manager');

        $this->publishes([
            __DIR__ . '/../Resources/Lang' => resource_path('lang/vendor/provision/media-manager'),
        ], 'lang');

        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'media-manager');

        $this->publishes([
            __DIR__ . '/../Public' => public_path('vendor/provision/media-manager'),
        ], 'public');

        $this->publishes([
            __DIR__ . '/../Config/media-manager.php' => config_path('media-manager.php'),
        ], 'config');

        \ProVision\Administration\Administration::bootModule('media-manager', Administration::class);

        \Form::component('mediaManager', 'media-manager::components.button', [
            'model',
            'config' => []
        ]);
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register() {

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/media-manager.php', 'media-manager'
        );

        $this->commands([
            MediaResize::class
        ]);

        //$this->app->register(RouteServiceProvider::class);
    }
}
