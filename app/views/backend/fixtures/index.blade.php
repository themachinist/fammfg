@extends('backend/layouts/default')

{{-- Page title --}}
@lang('admin/fixtures/general.tooling_fixtures') ::
@parent
@stop

{{-- Page content --}}
@section('content')


<div class="row header">
    <div class="col-md-12">
        <a href="{{ route('create/fixtures') }}" class="btn btn-success pull-right"><i class="fa fa-plus icon-white"></i> Create New</a>
        <h3>@lang('admin/fixtures/general.tooling_fixtures')</h3>
    </div>
</div>

<div class="row form-wrapper">
    {{ Datatable::table()
->addColumn(Lang::get('admin/fixtures/table.title'),
			Lang::get('admin/fixtures/table.serial'),
			Lang::get('admin/fixtures/form.copies'),
			Lang::get('admin/fixtures/form.available_copies'),
			Lang::get('admin/fixtures/form.notes'),
			Lang::get('table.actions'))
->setOptions(
	array(
		'language' => array(
			'search' => Lang::get('general.search'),
			'lengthMenu' => Lang::get('general.page_menu'),
			'loadingRecords' => Lang::get('general.loading'),
			'zeroRecords' => Lang::get('general.no_results'),
			'info' => Lang::get('general.pagination_info'),
			'processing' => Lang::get('general.processing'),
			'paginate'=> array(
				'first'=>Lang::get('general.first'),
				'previous'=>Lang::get('general.previous'),
				'next'=>Lang::get('general.next'),
				'last'=>Lang::get('general.last'),
			),
		),
		'sAjaxSource'=>route('api.fixtures.list'),
		'dom' =>'CT<"clear">lfrtip',
		'colVis'=> array('showAll'=>'Show All','restore'=>'Restore','exclude'=>array(6),'activate'=>'mouseover'),
		'columnDefs'=> array(
			array('bSortable'=>false,'targets'=>array(5)),
			array('width'=>'20%','targets'=>array(5)),
		),
		'order'=>array(array(0,'asc')),
	)
)
->render() }}
</div>
@stop
