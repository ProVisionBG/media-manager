<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager;

use Illuminate\Database\Eloquent\Model;

class MediaManagerTranslation extends Model
{

    protected $fillable = [
        'title',
        'description',
        'visible'
    ];

    protected $casts = [
        'visible' => 'boolean'
    ];

    /**
     * Customize slug engine.
     *
     * @param $engine
     * @return mixed
     */
    public function customizeSlugEngine($engine)
    {
        /*
         * @todo: да го добавя в config
         */
        $engine->addRule('ъ', 'a');
        $engine->addRule('щ', 'sht');
        $engine->addRule('ь', 'y');
        $engine->addRule('Ъ', 'A');
        $engine->addRule('Щ', 'SHT');

        return $engine;
    }
}
