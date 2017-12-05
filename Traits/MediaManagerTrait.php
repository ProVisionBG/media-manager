<?php
/**
 * Copyright (c) 2017. ProVision Media Group Ltd. <http://provision.bg>
 * Venelin Iliev <http://veneliniliev.com>
 */

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use ProVision\MediaManager\Events\ResizedEvent;
use ProVision\MediaManager\Models\MediaManager;

trait MediaManagerTrait {
    /**
     * boot trait.
     */
    public static function bootMediaManagerTrait() {
        static::deleting(function ($model) {
            /*
             * Ако модела не използва Soft Deleting изтриваме прикачените към него файлове!
             */
            $traits = class_uses($model);

            if (in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', $traits)) {
                // Model uses soft deletes - DON'T DELETE ATTACHED FILES
            } else {
                $mediaItems = $model->media()->get();

                if (!$mediaItems->isEmpty()) {
                    foreach ($mediaItems as $mediaItem) {
                        /*
                         * Изтриваме ги 1 по 1 за да може да изтрие и физически файла със boot()::deleting
                         */
                        $mediaItem->delete();
                    }
                }
            }
        });
    }

    /**
     * Resize image.
     *
     * @param MediaManager $media
     *
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function resize(MediaManager $media) {

        $file = $media->path . DIRECTORY_SEPARATOR . $media->file;

        /*
         * exists?
         */
        if (!$media->getStorageDisk()->exists($file)) {
            return false;
        }

        $imageContent = $media->getStorageDisk()->get($file);

        /*
         * Дали е картинка?
         */
        try {
            $imageMake = \Intervention\Image\Facades\Image::make($imageContent);
            if (!$media->is_image) {
                $media->is_image = true;
                $media->save();
            }
        } catch (\Exception $e) {
            if ($media->is_image) {
                $media->is_image = false;
                $media->save();
            }
            return false;
        }

        //_ - default image size for administration preview
        $image = $imageMake->fit(100, 100, function ($c) {
            $c->aspectRatio();
            $c->upsize();
        })->stream('jpg', 90);
        $media->getStorageDisk()->put($media->path . DIRECTORY_SEPARATOR . '_' . basename($file), $image->getContents());

        $sizes = config('provision_administration.image_sizes');

        if (empty($sizes)) {
            return true;
        }

        foreach ($sizes as $key => $size) {

            //check mode
            if (empty($size['mode']) || !in_array($size['mode'], [
                    'fit',
                    'resize',
                ])
            ) {
                \Debugbar::error('Image resize wrong mode! (key: ' . $key . ')');
                \Log::error('Image resize wrong mode! (key: ' . $key . ')');
                continue;
            }

            //set resize mode
            $mode = $size['mode'];

            //make resize
            $imageMake = \Intervention\Image\Facades\Image::make($imageContent);
            $imageResized = $imageMake->$mode($size['width'], $size['height'], function ($c) use ($size) {
                if (!empty($size['aspectRatio']) && $size['aspectRatio'] === true) {
                    $c->aspectRatio();
                }
                if (!empty($size['upsize']) && $size['upsize'] === true) {
                    $c->upsize();
                }
            })->stream('jpg', 90);

            $media->getStorageDisk()->put($media->path . DIRECTORY_SEPARATOR . $key . '_' . basename($file), $imageResized->getContents());
        }

        event(new ResizedEvent($media));

        return true;
    }


    /**
     * Оразмерява файл
     *
     * @param $file
     *
     * @return bool
     */
    public function resizeFile($file) {
        /*
        * Дали е картинка?
        */
        try {
            $imageMake = \Intervention\Image\Facades\Image::make($file);
        } catch (\Exception $e) {
            return false;
        }

        //_ - default image size for administration preview
        $imageMake->fit(100, 100, function ($c) {
            $c->aspectRatio();
            $c->upsize();
        })->save(dirname($file) . DIRECTORY_SEPARATOR . '_' . basename($file));

        $sizes = config('provision_administration.image_sizes');

        if (empty($sizes)) {
            return true;
        }

        foreach ($sizes as $key => $size) {

            //check mode
            if (empty($size['mode']) || !in_array($size['mode'], [
                    'fit',
                    'resize',
                ])
            ) {
                \Debugbar::error('Image resize wrong mode! (key: ' . $key . ')');
                \Log::error('Image resize wrong mode! (key: ' . $key . ')');
                continue;
            }

            //set resize mode
            $mode = $size['mode'];

            //make resize
            $imageMake = \Intervention\Image\Facades\Image::make($file);
            $imageMake->$mode($size['width'], $size['height'], function ($c) use ($size) {
                if (!empty($size['aspectRatio']) && $size['aspectRatio'] === true) {
                    $c->aspectRatio();
                }
                if (!empty($size['upsize']) && $size['upsize'] === true) {
                    $c->upsize();
                }
            })->save(dirname($file) . DIRECTORY_SEPARATOR . $key . '_' . basename($file));
        }

        return true;
    }

    /**
     * Media relation.
     *
     * @param bool $sub
     *
     * @return mixed
     */
    public function media($sub = false) {

        $relation = $this->morphMany(MediaManager::class, 'mediaable')
            ->orderBy('order_index', 'asc');

        if ($sub) {
            $relation->where('mediaable_sub_type', $sub);
        } else {
            $relation->whereNull('mediaable_sub_type');
        }

        return $relation;
    }

    /**
     * Relation::morphMap map
     *
     * @param Model $object | string
     *
     * @return string
     */
    public function getMediaableType($object) {
        if (is_object($object)) {
            $class = class_basename($object);
        } else {
            $class = $object;
        }

        $type = array_search($class, Relation::morphMap());
        if ($type) {
            return $type;
        }

        return $class;
    }
}
