<div
        class="media-item form-inline"
        data-id="{{$item->id}}"
        data-row="{{json_encode($item->toArray())}}"
>

    <div class="img">
        @if($item->is_image)
            <img title="{{$item->file}}" src="{{$item->getPublicPath('_')}}?time={{time()}}"/>
        @else
            <div class="file-icon file-icon-xl" title="{{$item->file}}"
                 data-type="{{\File::extension($item->file)}}"></div>
        @endif
        <input type="checkbox" name="selected[]" value="{{$item->id}}"/>
    </div>

    <div class="btn-group-vertical">
        <button type="button" class="btn btn-default btn-drag"><i class="fa fa-arrows"></i></button>
        <button type="button" class="btn btn-danger btn-delete"><i class="fa fa-trash-o"></i></button>

        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="{{$item->getPublicPath()}}" download="{{$item->file}}"><i
                                class="fa fa-download"></i> Свали файла</a></li>
                {{--<li><a href="#"><i class="fa fa-pencil-square-o"></i> Редактирай</a></li>--}}
                <li><a href="#" class="file-rename"><i class="fa fa-pencil-square-o"></i> Преименувай файла</a></li>
                <li><a href="#" class="edit-visibility"><i class="fa fa-eye-slash"></i> Описание / Видимост</a></li>
                <li><a href="#" class="rotate" data-angle="-90"><i class="fa fa-repeat"></i> Завъртане 90°</a></li>
                <li><a href="#" class="rotate" data-angle="90"><i class="fa fa-undo"></i> Завъртане -90°</a></li>
                <li><a href="#"><i class="fa fa-code"></i> Код за вграждане</a></li>
            </ul>
        </div>
    </div>

    {{--<div class="bottom-controls">--}}

    {{--<div class="input-group input-group-sm">--}}
    {{--<span class="input-group-addon">--}}
    {{--<input type="checkbox" name="selected[]" value="{{$item->id}}"/>--}}
    {{--</span>--}}
    {{--<div class="input-group-btn" class="media-choice-language">--}}
    {{--<button type="button" class="btn btn-default btn-selected-lang dropdown-toggl" data-toggle="dropdown"--}}
    {{--aria-haspopup="true" aria-expanded="false">--}}
    {{--<span class="lang-sm" lang="{{$item->lang}}"></span>--}}
    {{--<span class="caret"></span>--}}
    {{--</button>--}}
    {{--<ul class="dropdown-menu" role="menu">--}}
    {{--@foreach(\Administration::getLanguages() as $flag=>$language)--}}
    {{--<li><a href="javascript:void(0);" class="choice-lang" data-lang="{{$flag}}"><span--}}
    {{--class="lang-sm" lang="{{$flag}}"></span> {{$language['native']}}</a></li>--}}
    {{--@endforeach--}}
    {{--<li><a href="javascript:void(0);" class="choice-lang" data-lang=""><span class="lang-sm"--}}
    {{--lang=""></span> {{trans('administration::index.all')}}--}}
    {{--</a></li>--}}
    {{--</ul>--}}
    {{--</div>--}}

    {{--</div>--}}


    {{--</div>--}}
</div>