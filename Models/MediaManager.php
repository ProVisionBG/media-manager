<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use ProVision\MediaManager\Traits\MediaManagerTrait;

class MediaManager extends Model {
    use \Rutorika\Sortable\SortableTrait, Translatable, MediaManagerTrait;

    protected static $sortableGroupField = [
        'mediaable_type',
        'mediaable_sub_type',
        'mediaable_id',
    ];
    protected static $sortableField = 'order_index';
    public $translatedAttributes = [
        'title',
        'description',
        'visible',
        'link'
    ];
    public $table = 'media_manager';
    protected $appends = ['path'];

    protected $with = [
        'translations'
    ];

    protected $casts = [
        'is_image' => 'boolean'
    ];

    public static function boot() {
        static::deleting(function ($model) {
            /*
             * automatic remove files
             */
            if (File::exists(public_path($model->path))) {
                File::deleteDirectory(public_path($model->path));
            }
        });

        static::saving(function ($model) {
            if ($model->id) {
                //automatic create directories structure
                if (!File::exists(public_path($model->path))) {
                    File::makeDirectory(public_path($model->path), 0775, true, true);
                }


            }
        });

        static::saved(function ($model) {

            //automatic create directories structure
            if (!File::exists(public_path($model->path))) {
                File::makeDirectory(public_path($model->path), 0775, true, true);
            }

            /**
             * Сваляне на файл - ако бъде подаден като ->file = url
             */
            if (filter_var($model->file, FILTER_VALIDATE_URL)) {
                $contents = file_get_contents($model->file);
                $fileSavePath = public_path($model->path . basename($model->file));
                File::put($fileSavePath, $contents);
                $model->file = basename($fileSavePath);
                $model->save();
                $model->quickResize();
            }
        });

        parent::boot();
    }

    /**
     * @return string
     */
    public function getPathAttribute() {
        $path = 'uploads' . DIRECTORY_SEPARATOR . 'media-manager' . DIRECTORY_SEPARATOR . '' . strtolower(str_ireplace('\\', '-', $this->attributes['mediaable_type']));

        //mediaable_sub_type
        if (!empty($this->attributes['mediaable_sub_type'])) {
            $path .= DIRECTORY_SEPARATOR . strtolower($this->attributes['mediaable_sub_type']);
        }

        //mediaable_id
        $path .= DIRECTORY_SEPARATOR . $this->mediaable_id;

        //id
        $path .= DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * Връща пълният път до файла - ако е картинка може да се подаде и размер
     *
     * @param string $size
     *
     * @return string
     */
    public function getPublicPath($size = false) {
        $path = str_ireplace('\\', '/', $this->path);
        if ($size && $this->is_image) {
            if ($size == '_') {
                $path .= '_';
            } else {
                $path .= $size . '_';
            }
        }

        return asset($path . $this->file);
    }

    /**
     * quick resize media item
     */
    public function quickResize() {
        return $this->resize($this);
    }
}
