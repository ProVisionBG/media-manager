<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager;

use ProVision\Administration\AdminModelTranslations;

class MediaManagerTranslation extends AdminModelTranslations
{

    protected $fillable = [
        'title',
        'description',
        'visible'
    ];

    protected $casts = [
        'visible' => 'boolean'
    ];
}
