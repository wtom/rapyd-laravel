

@if (in_array("show", $actions))
<<<<<<< HEAD
    <a class="" title="@lang('rapyd::rapyd.show')" href="{{ $uri }}?show={{ $id }}"><span class="ic-globe"> </span></a>
@endif
@if (in_array("modify", $actions))
    <a class="" title="@lang('rapyd::rapyd.modify')" href="{{ $uri }}?modify={{ $id }}"><span class="fa fa-edit"> </span></a>
@endif
@if (in_array("delete", $actions))
    <a class="text-danger" title="@lang('rapyd::rapyd.delete')" href="{{ $uri }}?delete={{ $id }}"><span class="glyphicon glyphicon-trash"> </span></a>
=======
    <a class="" title="@lang('rapyd::rapyd.show')" href="{!! $uri !!}?show={!! $id !!}"><span class="glyphicon glyphicon-eye-open"> </span></a>
@endif
@if (in_array("modify", $actions))
    <a class="" title="@lang('rapyd::rapyd.modify')" href="{!! $uri !!}?modify={!! $id !!}"><span class="glyphicon glyphicon-edit"> </span></a>
@endif
@if (in_array("delete", $actions))
    <a class="text-danger" title="@lang('rapyd::rapyd.delete')" href="{!! $uri !!}?delete={!! $id !!}"><span class="glyphicon glyphicon-trash"> </span></a>
>>>>>>> upstream/2.0
@endif
