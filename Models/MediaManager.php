<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
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

    protected $appends = [
        'path'
    ];
    protected $with = [
        'translations'
    ];
    protected $casts = [
        'is_image' => 'boolean'
    ];

    public static function boot() {

        static::deleting(function ($model) {

            $disk = Storage::disk(config('media-manager.default_file_system_disk'));

            /**
             * Изтрива файловете 1 по 1 - за GCS
             */
            if ($files = $disk->files($model->path)) {
                $disk->delete($files);
                $disk->deleteDirectory($model->path);
            }

            if ($disk->exists($model->path)) {
                $disk->deleteDirectory($model->path);
            }

        });

        static::saving(function ($model) {
            if ($model->id) {
                //automatic create directories structure
                $disk = Storage::disk(config('media-manager.default_file_system_disk'));
                if (!$disk->exists($model->path)) {
                    $disk->makeDirectory($model->path);
                }
            }
        });

        static::saved(function ($model) {

            $disk = Storage::disk(config('media-manager.default_file_system_disk'));

            //automatic create directories structure
            if (!$disk->exists($model->path)) {
                $disk->makeDirectory($model->path);
            }

            /**
             * Сваляне на файл - ако бъде подаден като ->file = url
             */
            if (filter_var($model->file, FILTER_VALIDATE_URL)) {

                /*
                 * Сваляне на файла с cURL
                 */
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $model->file);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSLVERSION, 3);
                $contents = curl_exec($ch);
                curl_close($ch);

                $fileSavePath = $model->path . basename($model->file);
                $disk->put($fileSavePath, $contents);
                $model->file = basename($fileSavePath);
                $model->save();
                $model->quickResize();
            } elseif (file_exists($model->file)) {
                /**
                 * Ако файла е локален
                 */
                $contents = file_get_contents($model->file);
                $fileSavePath = $model->path . basename($model->file);
                $disk->put($fileSavePath, $contents);
                $model->file = basename($fileSavePath);
                $model->save();
                $model->quickResize();
            }
        });

        parent::boot();
    }

    /**
     * @param string $disk
     *
     * @return FilesystemAdapter
     */
    public function getStorageDisk($disk = null) {
        if (empty($disk)) {
            $disk = config('media-manager.default_file_system_disk');
        }
        return Storage::disk($disk);
    }

    /**
     * @return string
     */
    public function getPathAttribute() {
        $path = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'media-manager' . DIRECTORY_SEPARATOR . '' . strtolower(str_ireplace('\\', '-', $this->attributes['mediaable_type']));

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
     * @param bool|string $size
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

        return $this->getStorageDisk()->url($path . $this->file);
    }

    /**
     * quick resize media item
     */
    public function quickResize() {
        return $this->resize($this);
    }
}
