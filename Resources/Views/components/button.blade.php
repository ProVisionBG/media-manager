<?php
$elementID = 'adminMediaButton-' . str_random(20);
$options = \ProVision\MediaManager\MediaManager::config(array_replace_recursive(['filters' => ['mediaable_type' => get_class($model), 'mediaable_id' => $model->id]], $options), 'array');
?>
<button
        title="{{$options['button']['title']}}"
        class="media-manager btn btn-sm {{$options['button']['class']}}"
        data-config='{{json_encode($options)}}'
>
    <i class="fa fa-{{$options['button']['icon']}}" aria-hidden="true"></i>
</button>
