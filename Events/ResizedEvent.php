<?php
/**
 * Copyright (c) 2017. ProVision Media Group Ltd. <http://provision.bg>
 * Venelin Iliev <http://veneliniliev.com>
 */

namespace ProVision\MediaManager\Events;

use Illuminate\Queue\SerializesModels;
use ProVision\MediaManager\Models\MediaManager;

class ResizedEvent {
    use SerializesModels;

    /**
     * @var MediaManager
     */
    public $mediaManager;

    /**
     * Create a new event instance.
     *
     * @param MediaManager $mediaManager
     */
    public function __construct(MediaManager $mediaManager) {
        $this->mediaManager = $mediaManager;
    }
}