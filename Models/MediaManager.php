<?php

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Lang;
use ProVision\MediaManager\Traits\MediaManagerTrait;

class MediaManager extends Model
{
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
        'visible'
    ];
    public $table = 'media_manager';
    protected $with = ['translations'];
    protected $appends = ['path'];

    protected $casts = [
        'is_image' => 'boolean'
    ];

    public static function boot()
    {
        static::deleting(function ($model) {
            /*
             * automatic remove files
             */
            if (\File::exists(public_path($model->path))) {
                \File::deleteDirectory(public_path($model->path));
            }
        });

        static::saving(function ($model) {
            if ($model->id) {
                //automatic create directories structure
                if (!\File::exists(public_path($model->path))) {
                    \File::makeDirectory(public_path($model->path, 0775, true));
                }
            }
        });

        parent::boot();
    }

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

    /*
     * quick resize media item
     */

    public function getPathAttribute()
    {
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

    public function quickResize()
    {
        $this->resize($this);
    }
}
