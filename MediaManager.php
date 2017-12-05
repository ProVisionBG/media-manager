<?php
/**
 * Copyright (c) 2017. ProVision Media Group Ltd. <http://provision.bg>
 * Venelin Iliev <http://veneliniliev.com>
 */

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Lang;

class MediaManager
{
    /**
     * Array config for JS
     * @param array $config
     * @return array
     */
    public static function config($config = [], $return = 'json')
    {

        /*
         * Relation::morphMap fix
         */
        if (!empty($config['filters']['mediaable_type'])) {
            $type = array_search($config['filters']['mediaable_type'], Relation::morphMap());
            if ($type) {
                $config['filters']['mediaable_type'] = $type;
            }
        }

        /*
         * merge/replace configs
         */
        $array = array_replace_recursive([
            'routes' => [
                'index' => \ProVision\Administration\Administration::route('media-manager.index'),
                //'store' => \ProVision\Administration\Administration::route('media-manager.store'),
                //'destroy' => \ProVision\Administration\Administration::route('media-manager.destroy', [0]),
            ],
            'lang' => Lang::get('media-manager::admin'),
            'languages' => \ProVision\Administration\Administration::getLanguages(),
            'button' => [
                'title' => trans('media-manager::admin.button_title'),
                'class' => 'btn-warning',
                'icon' => 'picture-o'
            ]
        ], $config);

        if ($return == 'json') {
            return json_encode($array);
        }

        return $array;
    }


}
