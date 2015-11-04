@extends('backend/layouts/default')

@section('title0')

    @if (Input::get('status'))
        @if (Input::get('status')=='Pending')
            @lang('general.pending')
        @elseif (Input::get('status')=='RTD')
            @lang('general.ready_to_deploy')
        @elseif (Input::get('status')=='Undeployable')
            @lang('general.undeployable')
        @elseif (Input::get('status')=='Deployable')
            @lang('general.deployed')
         @elseif (Input::get('status')=='Requestable')
            @lang('admin/assets/general.requestable')
        @elseif (Input::get('status')=='Archived')
            @lang('general.archived')
         @elseif (Input::get('status')=='Deleted')
            @lang('general.deleted')
        @endif
    @else
            @lang('general.all')
    @endif

    @lang('general.assets')
@stop

{{-- Page title --}}
@section('title')
    @yield('title0') :: @parent
@stop

{{-- Page content --}}
@section('content')

<style>
.dataTables_filter {padding-right: 20px;}

</style>

<div class="row header">
    <div class="col-md-12">
        <a href="{{ route('create/asset') }}" class="btn btn-success pull-right"><i class="fa fa-plus icon-white"></i> @lang('general.create')</a>
        <h3>@yield('title0')</h3>
    </div>
</div>

<div class="row">



 {{ Form::open([
      'method' => 'POST',
      'route' => ['assets/bulkedit'],
	  'class' => 'form-horizontal' ]) }}



{{ Datatable::table()
    ->addColumn('<div class="text-center"><input type="checkbox" id="checkAll" style="padding-left: 0px;"></div>',Lang::get('admin/assets/form.name'),
    	Lang::get('admin/assets/table.asset_tag'),
    	Lang::get('admin/assets/table.serial'),
		Lang::get('admin/assets/form.model'),
    	Lang::get('admin/assets/table.status'),
		Lang::get('admin/assets/table.location'),
    	Lang::get('general.category'),
    	Lang::get('admin/assets/table.eol'),
        Lang::get('general.notes'),
        Lang::get('admin/assets/form.mac_address'),
        Lang::get('admin/assets/form.order'),
    	Lang::get('admin/assets/table.checkout_date'),
    	Lang::get('admin/assets/table.change'),
    	Lang::get('table.actions'))
    ->setOptions(
            array(
	            'language' => array(
	            	'search' => Lang::get('general.search'),
	            	'lengthMenu' => Lang::get('general.page_menu'),
	            	'loadingRecords' => Lang::get('general.loading'),
	            	'zeroRecords' => Lang::get('general.no_results'),
	            	'info' => Lang::get('general.pagination_info'),
	            	'processing' => '<i class="fa fa-spinner fa-spin"></i> '.Lang::get('general.processing'),
	            	'paginate'=> array(
	            		'first'=>Lang::get('general.first'),
	            		'previous'=>Lang::get('general.previous'),
	            		'next'=>Lang::get('general.next'),
	            		'last'=>Lang::get('general.last'),
	            		),
	            	),
            	'sAjaxSource'=> route('api.assets.list', array(''=>Input::get('status'),'order_number'=>Input::get('order_number'))),
                'dom' =>'CT<"clear">lfrtip',
                'colVis'=> array('showAll'=>'Show All','restore'=>'Restore','exclude'=>array(0,13,14),'activate'=>'mouseover'),
                'columnDefs'=> array(array('visible'=>false,'targets'=>array(7,8,9)),array('orderable'=>false,'targets'=>array(0,13,14))),
                'order'=>array(array(1,'asc')),
            )
        )
    ->render('backend/assets/datatable') }}

 {{ Form::close() }}

</div>

<script>

	$(function() {

		function checkForChecked() {

	        var check_checked = $('input.one_required:checked').length;

	        if (check_checked > 0) {
	            $('#bulkEdit').removeAttr('disabled');
	        }
	        else {
	            $('#bulkEdit').attr('disabled', 'disabled');
	        }
	    }

	    $('table').on('change','input.one_required',checkForChecked);

	    $("#checkAll").change(function () {
			$("input:checkbox").prop('checked', $(this).prop("checked"));
			checkForChecked();
		});

	});


</script>



@stop
