@if(!empty($items) && !$items->isEmpty())
    @if(\Request::has('mode') && \Request::input('mode')=='ckeditor')
        @each('media-manager::ckeditor_item', $items, 'item')
    @else
        @each('media-manager::item', $items, 'item')
    @endif
@else
    <div class="callout callout-info">
        <p>{{trans('media-manager::admin.not_found_items')}}</p>
    </div>
@endif