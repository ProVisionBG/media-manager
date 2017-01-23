<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager;

use Illuminate\Support\Facades\Route;
use Kris\LaravelFormBuilder\Form;
use ProVision\Administration\Contracts\Module;
use ProVision\MediaManager\Http\Controllers\MediaManagerController;

class Administration implements Module
{

    public function routes($module)
    {
        Route::resource('media-manager', MediaManagerController::class);
    }

    public function dashboard($module)
    {

    }

    public function menu($module)
    {
    }

    /**
     * Add settings in administration panel
     * @param $module
     * @param Form $form
     * @return mixed
     */
//    public function settings($module, Form $form)
//    {
//        // TODO: Implement settings() method.
//    }
}
