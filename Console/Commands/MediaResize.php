<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ProVision\MediaManager\Models\MediaManager;

class MediaResize extends Command {
    /**
     * Name of the command.
     *
     * @param string
     */
    protected $name = 'admin:media-manager:resize';

    /**
     * Necessary to let people know, in case the name wasn't clear enough.
     *
     * @param string
     */
    protected $description = 'Resize items in MediaManager';

    public function __construct() {
        /*
        * command fix
        */
        $this->signature = config('provision_administration.command_prefix') . ':media-manager:resize';

        parent::__construct();
    }

    /**
     * Run the package migrations.
     */
    public function handle() {
        MediaManager::where('is_image', 1)
            ->chunk(100, function ($media) {
                foreach ($media as $m) {

                    /*
                     * remove old sizes
                     */
                    $filesInDirectory = $media->getStorageDisk()->files(realpath($m->path));
                    if (!empty($filesInDirectory)) {
                        foreach ($filesInDirectory as $file) {
                            //дали е размер или оригинал? - запазваме оригинала
                            if (strstr(basename($file), '_')) {
                                $media->getStorageDisk()->delete($file);
                            }
                        }
                    }

                    /*
                     * make new sizes
                     */
                    $m->quickResize();

                    $this->info('#' . $m->id . ': resized');
                }
            });

        $this->info($this->signature . ' END');
    }
}
