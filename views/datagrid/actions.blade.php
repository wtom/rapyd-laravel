@if (in_array("show", $actions))
    <a class="" title="@lang('rapyd::rapyd.show')" href="{!! url('panel/'.$current_entity.'/'.$uri) !!}?show={!! $id !!}"><span class="glyphicon glyphicon-list-alt"> </span></a>
@endif
@if (in_array("modify", $actions))
    <a class="" title="@lang('rapyd::rapyd.modify')" href="{!! url('panel/'.$current_entity.'/'.$uri) !!}?modify={!! $id !!}"><span class="fa fa-edit"> </span></a>
@endif
@if (in_array("codes", $actions))
    <a class="" title="@lang('Codes')" href="{!! url('panel/Voucher/all') !!}?campaign={!! $id !!}"><i class="fa fa-list-ol"> </i></a>
@endif
@if (in_array("generate", $actions))
    <a class="" title="@lang('Generate')" href="{!! url('panel/'.$current_entity.'/'.$uri) !!}?generate={!! $id !!}"><i class="fa fa-file-text"> </i></a>
@endif
@if (in_array("delete", $actions))
    <a class="text-danger" title="@lang('rapyd::rapyd.delete')" href="{!! url('panel/'.$current_entity.'/'.$uri) !!}?delete={!! $id !!}"><span class="glyphicon glyphicon-trash"> </span></a>
@endif
