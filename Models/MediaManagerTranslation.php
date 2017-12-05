<?php
/**
 * Copyright (c) 2017. ProVision Media Group Ltd. <http://provision.bg>
 * Venelin Iliev <http://veneliniliev.com>
 */

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Models;

use ProVision\Administration\AdminModelTranslations;

class MediaManagerTranslation extends AdminModelTranslations
{

    protected $fillable = [
        'title',
        'description',
        'visible',
        'link'
    ];

    protected $casts = [
        'visible' => 'boolean'
    ];
}
