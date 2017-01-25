<?php
$elementID = 'adminMediaButton-' . str_random(20);
$config = \ProVision\MediaManager\MediaManager::config(array_replace_recursive(['filters' => ['mediaable_type' => get_class($model), 'mediaable_id' => $model->id]], $config), 'array');
?>
<button
        title="{{$config['button']['title']}}"
        class="media-manager btn btn-sm {{$config['button']['class']}}"
        data-config='{{json_encode($config)}}'
>
    <i class="fa fa-{{$config['button']['icon']}}" aria-hidden="true"></i>
</button>
